<?php

namespace WebChemistry\Images\Image;

use Nette, Nette\Utils\Strings, WebChemistry;

class PropertyAccess extends Nette\Object implements IImage {

    protected $assetsDir;
    
    private $name;
    
    private $namespace;
    
    private $height;
    
    private $width;
    
    private $flag = 0;
    
    private $crop = array();
    
    protected $prefix;
    
    private $baseUri = FALSE;
    
    public function __construct($assetsDir) {
        $this->assetsDir = rtrim($assetsDir, '/\\') . '/';
    }
    
    public function getPrefix() {
        return $this->prefix;
    }
    
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        
        return $this;
    }
    
    public function getFlag() {
        return $this->flag;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getNamespace() {
        return $this->namespace;
    }
    
    public function getWidth() {
        return $this->width;
    }
    
    public function getHeight() {
        return $this->height;
    }
    
    public function isBaseUri() {
        return $this->baseUri;
    }
    
    public function getCrop() {
        return $this->crop;
    }
    
    public function setSize($size) {
        if (preg_match('#crop\(([0-9%,\s]+)\)+#', $size, $matches)) {
            $attrs = array();
            $explode = explode(',', $matches[1]);
            
            foreach ($explode as $value) {
                $trim = trim($value);
                
                $attrs[] = $trim;
            }
            
            $this->crop = $attrs;
            
            return $this;
        }
        
        $explode = explode('x', $this->parseString($size));
        
        if (count($explode) > 2) {
            throw new WebChemistry\Images\ImageStorageException('Size have more than 2 sizes.');
        }
        
        if (count($explode) === 2) {
            $this->width = strpos($explode[0], '%') === FALSE ? $this->checkNum($explode[0]) : $explode[0];
            $this->height = strpos($explode[1], '%') === FALSE ? $this->checkNum($explode[1]) : $explode[1];
        } else {
            $this->width = strpos($explode[0], '%') === FALSE ? $this->checkNum($explode[0]) : $explode[0];
            $this->height = NULL;
        }
        
        return $this;
    }
    
    public function setAbsoluteName($name) {
        if (!$name) {
            return $this;
        }
        
        if (Strings::startsWith($name, '//')) {
            $this->baseUri = TRUE;
        }
        
        $name = $this->parseString($name);
        
        $explode = explode('/', $name);
        
        $this->name = end($explode);
        array_pop($explode);
        $this->namespace = $explode ? implode('/', $explode) : NULL;
        
        return $this;
    }
    
    public function setName($name) {
        $this->name = $this->parseString($name);
        
        if (strpos($this->name, '/') !== FALSE) {
            throw new WebChemistry\Images\ImageStorageException('Name of image must not contain /');
        }
        
        return $this;
    }
    
    public function setHeight($height) {
        $this->checkNum($height);
        
        $this->height = $height;
        
        return $this;
    }
    
    public function setWidth($width) {
        $this->checkNum($width);
        
        $this->width = $width;
        
        return $this;
    }
    
    public function setNamespace($namespace) {
        $this->namespace = $this->parseString($namespace);
        
        if ($this->namespace === Info::ORIGINAL) {
            throw new WebChemistry\Images\ImageStorageException('Namespace must not same name as original directory.');
        }
        
        return $this;
    }
    
    public function setIntegerFlag($flag) {
        if (!is_numeric($flag)) {
            throw new WebChemistry\Images\ImageStorageException('Flag muset be integer in PropertyAccess::setIntegerFlag');
        }
        
        $this->flag = (int) $flag;
    }
    
    public function setFlag($flag) {
        $return = 0;
        
        foreach ((array) $flag as $row) {
            $return += $this->flagToInteger($row);
        }
        
        $this->flag = $return;
    }
    
    private function flagToInteger($flag) {
        $flag = trim(Strings::upper($flag));
        
        $value = @constant('Nette\Utils\Image::' . $flag);
        
        if ($value === NULL) {
            throw new WebChemistry\Images\ImageStorageException("WebChemistry\Images: Flag '$flag' does not exist in Nette\Utils\Image.");
        }
        
        return $value;
    }
    
    private function checkNum($num) {
        $parse = rtrim($num, '%');
        
        if ($parse && !is_numeric($parse)) {
            throw new WebChemistry\Images\ImageStorageException('Height and width must be integer or percent.');
        }
        
        return $parse ? (int) $parse : NULL;
    }
    
    private function parseString($str) {
        return trim(trim($str), '/');
    }
}
