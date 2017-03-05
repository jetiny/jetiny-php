<?php
namespace Jetiny\Base;
use Jetiny\Base\Util;

class Paging
{
    
    public function parse($arr) {
        $opts = $this->_options;
        $keys 	= $opts['keys'];
        $pageNo = $keys['no'];
        $pageNo = isset($arr[$pageNo]) ? intval($arr[$pageNo]) : 1;
        if ($pageNo < 1) {
            $pageNo = 1;
        }
        $pageStep = $keys['step'];
        $pageStep =  isset($arr[$pageStep]) ? intval($arr[$pageStep]) : $opts['page_step_default'];
        if ($pageStep < $opts['page_step_min'] || $pageStep > $opts['page_step_max']) {
            $pageStep = $opts['page_step_default'];
        }
        if ($opts['page_step_enum']) {
            if (!in_array($pageStep, $opts['page_steps'])) {
                $pageStep = $opts['page_step_default'];
            }
        }
        return [
            'no'   => $pageNo,
            'step' => $pageStep,
            'start'=> $pageStep * ($pageNo -1)
        ];
    }
    /**
    * 
    * @param integer $step    记录分页
    * @param integer $count   总记录数
    * @param integer $no      当前页
    * @param array $query     参数组
    * 
    * @return array | null
    */
    public function page($step, $count, $no, $query = []) {
        $opts = $this->_options;
        $keys 	= $opts['keys'];
        $keyNo = $keys['no'];
        $query[$opts['keys']['step']] = $step;
        $total = ceil($count / $step );
        if ($info = Util::pageRange($total , $no, $opts['page_size'])) {
            list ($curr, $start, $end) = $info;
            
            $urls = []; //这个使用关联数组
            for($i = $start; $i <= $end; ++$i) {
                $query[$keyNo] = $i;
                $urls[$i] = http_build_query($query);
            }
            
            $r = Util::pageExtend($curr, $start, $end, $total);
            foreach($r as $k => & $v) {
                if ($v) {
                    $query[$keyNo] = $v;
                    $v = http_build_query($query);
                }
            }
            $r['curr']  = $curr;     //当前页序号
            $r['pages'] = $total;    //总页数
            $r['records'] = $count;  //总记录数
            
            $r['urls'] = $urls;     //页码组
            return $r;
        }
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
    
    protected $_options = [
        'keys' => [                              //分页键名
            'no' => 'no',
            'step' => 'step',
        ],
        'page_step_default' => 20,               //默认分页个数
        'page_step_min'     => 10,               //最小分页个数
        'page_step_max'     => 100,              //最大分页个数
        'page_step_enum'    => true,             //枚举方式,只支持列表中的数据
        'page_steps'        => [10, 20, 50,100], //枚举方式区间
        //页码分页
        'page_size'         => 10,               //展示的分页个数
    ];
}
