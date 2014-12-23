<?php

namespace WebChemistry\Images\Helpers;

use Nette, WebChemistry;

use Nette\Utils\Strings, Nette\Utils\Image;

/**
 * @property-read WebChemistry\Images\Root $parent
 */
class Helper extends Nette\ComponentModel\Component {
    
    /**
     * Check and edit directory's name
     * 
     * @param string $name
     * @return string
     */
    public function checkName($name) {
        if (Strings::startsWith($name, '/')) {
            $name = substr($name, 1);
        }
        
        if (Strings::endsWith($name, '/')) {
            $name = substr($name, 0. -1);
        }
        
        return $name;
    }
    
    /**
     * Explode width and height from string
     * 
     * @param string $size
     * @return array
     */
    public function size($size) {
        if (!$size) {
            return NULL;
        }
        
        $explode = explode('x', $size);
        
        if (count($explode) === 1) {
            $explode[1] = NULL;
        }
        
        return $explode;
    }
    
    /**
     * Get int from image's constants
     * 
     * @param string $flag
     * @return int
     * @throws WebChemistry\Images\ImageStorageException
     */
    protected function flagToInteger($flag) {
        $flag = trim(Strings::upper($flag));
        
        $value = @constant('Nette\Utils\Image::' . $flag);
        
        if ($value === NULL) {
            throw new WebChemistry\Images\ImageStorageException("WebChemistry\Images: Flag '$flag' does not exist in Nette\Utils\Image.");
        }
        
        return $value;
    }
    
    /**
     * Split flags and return intreger
     * 
     * @param array|string $flags
     * @return int
     */
    public function flags($flags) {
        $flag = 0;
        
        foreach ((array) $flags as $value) {
            $flag += $this->flagToInteger($value);
        }
        
        return $flag;
    }
    
    /**
     * Get namespace and filename from shortname
     * 
     * @param string $name
     * @return array
     */
    public function name($name) {
        $explode = explode('/', $name);
        
        $filename = end($explode);
        $namespace = count($explode) === 2 ? $explode[0] : NULL;
        
        return [
            $namespace,
            $filename
        ];
    }
    
    /**
     * Get name of resized dir
     * 
     * @param array|string $size
     * @param int $flags
     * @return string
     */
    public function resizedImageDir($size = NULL, $flags = NULL) {
        if (!$size) {
            return $this->parent->original;
        }
        
        return (is_array($size) ? implode('x', $size) : $size) . '_' . $flags;
    }
}
