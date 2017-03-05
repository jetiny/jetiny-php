<?php
namespace Jetiny\Cache;

class FileCache extends CacheHandler
{
    
    public function setup($options)
    {
        parent::setup($options);
        $this->setDirectoy($options['directory']);
        if (isset($options['extension']))
            $this->extension = $options['extension'];
        if (isset($options['length']))
            $this->length = $options['length'];
        if (isset($options['deepth']))
            $this->deepth = $options['deepth'];
    }
    
    public function setDirectoy($directory)
    {
        if ( ! is_dir($directory) && ! @mkdir($directory, 0777, true)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $directory
            ));
        }
        if ( ! is_writable($directory)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $directory
            ));
        }
        $this->directory = realpath($directory);
    }
    
    protected function doFetch($id)
    {
        $data     = '';
        $lifetime = -1;
        $filename = $this->getFilename($id);
        if ( ! is_file($filename)) {
            return false;
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (integer) $line;
        }
        if ($lifetime !== 0 && $lifetime < time()) {
            fclose($resource);
            return false;
        }
        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }
        fclose($resource);
        return unserialize($data);
    }
    protected function doContains($id)
    {
        $lifetime = -1;
        $filename = $this->getFilename($id);
        if ( ! is_file($filename)) {
            return false;
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (integer) $line;
        }
        fclose($resource);
        return $lifetime === 0 || $lifetime > time();
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }
        $data      = serialize($data);
        $filename  = $this->getFilename($id);
        return $this->writeFile($filename, $lifeTime . PHP_EOL . $data);
    }
    protected function getFilename($id)
    {
        $str = md5($id);
        $pos = $this->length * $this->deepth;
        return $this->directory
            . DIRECTORY_SEPARATOR
            . implode(str_split(substr($str, 0, $pos), $this->length), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . substr($str, $pos, 32)
            . preg_replace($this->disallowedCharacterPatterns, $this->replacementCharacters, $id)
            . $this->extension;
    }
    protected function doDelete($id)
    {
        return @unlink($this->getFilename($id));
    }
    protected function doFlush()
    {
        foreach ($this->getIterator() as $name => $file) {
            @unlink($name);
        }
        return true;
    }
    private function createPathIfNeeded($path)
    {
        if ( ! is_dir($path)) {
            if (false === @mkdir($path, 0777, true) && !is_dir($path)) {
                return false;
            }
        }
        return true;
    }
    protected function writeFile($filename, $content)
    {
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);
        if ( ! $this->createPathIfNeeded($filepath)) {
            return false;
        }
        if ( ! is_writable($filepath)) {
            return false;
        }
        $tmpFile = tempnam($filepath, 'swap');
        if (file_put_contents($tmpFile, $content) !== false) {
            if (@rename($tmpFile, $filename)) {
                @chmod($filename, 0666 & ~umask());
                return true;
            }
            @unlink($tmpFile);
        }
        return false;
    }
    private function getIterator()
    {
        return new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory)),
            '/^.+' . preg_quote($this->extension, '/') . '$/i'
        );
    }
    protected $directory;
    protected $extension = '.php';
    // 文件存放规则 md5($id)
    protected $length = 3; // 目录名长度  16^n = 16/256/4096/65536(4)
    protected $deepth = 3; // 目录深度
    private $disallowedCharacterPatterns = array(
        '/\-/', // replaced to disambiguate original `-` and `-` derived from replacements
        '/[^a-zA-Z0-9\-_\[\]]/' // also excludes non-ascii chars (not supported, depending on FS)
    );
    private $replacementCharacters = array('__', '-');
}
