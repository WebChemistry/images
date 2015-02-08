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
    
    public function setSize($size) {
        $explode = explode('x', $this->parseString($size));
        
        if (count($explode) === 2) {
            $this->width = $this->checkNum($explode[0]);
            $this->height = $this->checkNum($explode[1]);
        } else {
            $this->width = $this->checkNum($explode[0]);
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
        $this->namespace = count($explode) === 2 ? $explode[0] : NULL;
        
        return $this;
    }
    
    public function setName($name) {
        $this->name = $this->parseString($name);
        
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
        
        return $parse ? $parse : NULL;
    }
    
    private function parseString($str) {
        return trim(trim($str), '/');
    }
}
