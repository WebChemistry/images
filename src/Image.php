<?php

namespace WebChemistry\Images;

use Nette;

/**
 * @property-read string $path Full path of image (original)
 * @property-read string $shortname Shortname to use in macros
 */
class Image extends Nette\Object {
    
    /** @var string */
    private $namespace, $filename, $path;
    
    /**
     * @param string $namespace
     * @param string $filename
     * @param string $path
     */
    public function __construct($namespace, $filename, $path) {
        $this->namespace = $namespace;
        $this->filename = $filename;
        $this->path = $path;
    }
    
    /**
     * Full path of image (Original)
     * 
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
    
    /**
     * Shortname to use in macros
     * 
     * @return string
     */
    public function getShortname() {
        return ($this->namespace ? $this->namespace . '/' : '') . $this->filename;
    }
    
    /**
     * Shortname to use in macros
     * 
     * @return string
     */
    public function __toString() {
        return $this->getShortname();
    }
}
