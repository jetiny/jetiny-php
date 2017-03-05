<?php
namespace Jetiny\Base;

class Template
{
    public function __construct(){
        global $G;
        if (is_null($G))
            $G = array();
    }
    
    public function __call($method, $args)
    {
        if (isset($this->_methods[$method])) {
            return call_user_func_array($this->_methods[$method], $args);
        }
    }
    
    public function addMethod($key, $value)
    {
        $this->_methods[$key] = $value ;
    }
    
    public function display ($tpl, $key = '', $expire = 0 ){
        echo $this->import($tpl, $key, $expire);
    }
    
    function import ($tpl, $key = '', $expire = 0 ) {
        $root   = $this->option('path');
        $suffix = $this->option('suffix');
        $cache_tmpl = $this->option('cache_tmpl');
        $cache_path = $this->option('cache_path');
        
        $tpl_file = "{$root}{$tpl}{$suffix}"; // tpl source file path;
	    if ( !($cache_tmpl && $cache_path) ) {
            $ctx = file_get_contents($tpl_file);
	    	$ctx = $this->compile($ctx);
            $ctx = $this->runImport($ctx);
            return $ctx;
	    }
        
        $cache_sufix = $this->option('cache_sufix');
        
        if ( empty($key) ) {// mkpath
            $cache_file = $cache_path . str_replace('/','_', $tpl) . $cache_sufix; // compile file path;
        } else {
            $cache_level = $this->option('cache_level');
            $arys = str_split($md5k = md5($key), 2);
            if ($cache_level > 5)
                $cache_level = 5;
            for ($i = 0; $i++ < $cache_level; ){
                $cache_path .= $arys[$i]."/";
                if(!is_dir($cache_path)){
                    mkdir($cache_path);
                    chmod($cache_path,755);
                }
            }
            $cache_file = $cache_path . str_replace('/','_',$tpl) . $cache_sufix;
            $data_file  = $cache_path . str_replace('/','_',$tpl) . $md5k . $cache_sufix ;
        }
        
        $cache_debug = $this->option('cache_debug');
        
        $ctx = null;
    	if( !is_readable($cache_file)  // cache file no found
            || filemtime($tpl_file) > filemtime($cache_file) // file is changed
            ){
            if(is_readable($tpl_file)){
        	    $ctx =  file_get_contents($tpl_file);
        	    $ctx =  $this->compile($ctx);
        	    file_put_contents($cache_file, $ctx); //override cache file
            }
            else
                $ctx = '';
    	}
        if($ctx === '') // empty content
            return $ctx;
        
        $cache_data = $this->option('cache_data');
        
        if( !$cache_data    // no data cache
            || !$expire     // no data cache expire
            || !is_readable($data_file) // data cache file not found
            || filemtime($cache_file) > ($tc = filemtime($data_file)) // cache file changed
            || ($tc + $expire - $_SERVER['REQUEST_TIME']) < 0 // data cache file expire
            ){
            
            $inc = $cache_debug || (!$ctx);
            $ctx = $this->runImport($inc ? $cache_file : $ctx , $inc);
            
            if ($cache_data && $expire) { // save data cache
                file_put_contents($data_file, $ctx);
            }
            return $ctx;
        }
        return file_get_contents($data_file);
	}
    protected function runImport(/* $str $include */)
    {
        ob_start();
        global $G;
		extract($this->_vars);
        if (func_get_arg(1) === true)
            include func_get_arg(0);
        else
            eval('?>'.func_get_arg(0));
		return ob_get_clean();
    }
    
    function replace($str, $args = array())
    {
		return $this->runReplace($this->compile($str), $args);
	}
    
    protected function runReplace(/* $str, $arr*/)
    {
        ob_start();
        global $G;
		extract(func_get_arg(1));
        eval('?>'.func_get_arg(0));
		return ob_get_clean();
    }
    
    public function compile($str)
    {
		$ctx = preg_replace_callback($this->_compile_regexp, function($ma) {
			$tag = $ma[4];
		    if (($flag = substr($tag, 0, 1)) && ($name = substr($tag, 1)) ) {
                if('"' == $flag){ // string start with "
    		        return  "<?php echo {$tag}; ?>";
    		    }
                else if('~' == $flag){ // special string
                        if ($name[0] === '@')
                            $name = substr($name, 1);
    		        return  "<?php echo htmlspecialchars({$name});?>";
    		    }
                else if('@' == $flag){ // call function
    		        return  "<?php echo {$name}; ?>";
    		    }
                else if('$' == $flag){
                    if (preg_match('/[=(]/', $name)) {// $var =  $[
                        if ($name[0] === '$') { // $$import() => $this->import
                            return  "<?php echo \$this->" .substr($name, 1). "; ?>";
                        }
                    }
                    else // single variable
                        return  "<?php echo \${$name}; ?>";
    		    }
			}
			return '<?php '.$tag.' ?>';
		}, $str);
        
        if ($this->option('compress')) {
            $ctx = preg_replace(array_keys($this->_replaces) , array_values($this->_replaces), $ctx);
        }
		return $ctx;
    }
    
    public function option($key) {
        if (isset($this->_options[$key]))
            return $this->_options[$key];
    }
    
    public function setOptions($options) {
        if ($options) {
            $this->_options = array_replace($this->_options, $options);
        }
    }
    
    protected $_options = array(
		'path'=>'',
		'suffix'=>'.html',
        'compress' => false,        // enable compress
        'cache_tmpl' => false,      // enable cache compiled file
        'cache_data' => false,      // enable cache compiled buffer
        'cache_debug' => false,     // enable cache file debug with codelobster etc.
		'cache_path'=>'',
		'cache_sufix'=>'.php',
		'cache_level'=>3,
    );
    
    public function assign($key, $value)
    {
        $this->_vars[$key] = $value;
    }
    protected $_vars = array();
    protected $_methods = array();
    protected $_replaces = array(
        // combine php tag
        '/\?><\?php\ /' => '',
        // replace multi space to one @SEE http://stackoverflow.com/questions/5312349/minifying-final-html-output-using-regular-expressions-with-codeigniter
        '%(?>[^\S ]\s*| \s{2,})(?=[^<]*+(?:<(?!/?(?:textarea|pre|script)\b)[^<]*+)*+(?:<(?>textarea|pre|script)\b| \z))%six' => ' ',
    );
    //protected $_compile_regexp = '/([\s|\n|\r|\t]*)(\{\%([\s|\n|\r|\t]*))([\s\S]+?)(([\s|\n|\r|\t]*)\%\})([\s|\n|\r|\t]*)/is';
    protected $_compile_regexp = '/([\s|\n|\r|\t]*)(\<\?(?:php)?([\s|\n|\r|\t]*))([\s\S]+?)(([\s|\n|\r|\t]*)\?\>)([\s|\n|\r|\t]*)/is';
}

