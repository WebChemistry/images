<?php

namespace WebChemistry\Images\Image;

use Nette;

class Container extends PropertyAccess {
    
    public function getImageClass() {
        return $this->createImageInfo($this)->getImageClass();
    }
    
    public function getUniqueImage() {
        $info = $this->createImageInfo($this);
        
        while (file_exists($info->getAbsolutePath())) {
            $this->prefix = Nette\Utils\Random::generate();
            
            $info = $this->createImageInfo($this);
        }
        
        return $info;
    }
    
    public function isResize() {
        return $this->getWidth() || $this->getHeight() || $this->getCrop();
    }
    
    /**
     * @return Info
     */
    public function createImageInfo(IImage $class = NULL) {
        if ($class === NULL) {
            return new Info($this, $this->assetsDir);
        }
        
        return new Info($class, $this->assetsDir);
    }
}
