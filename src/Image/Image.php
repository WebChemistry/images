<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Image extends Container {
    
    protected $noImage;
    
    protected $basePath;
    
    protected $original;
    
    protected $originalSelf;
    
    public function __construct($assetsDir, $absoluteName, $noImage, $basePath) {
        parent::__construct($assetsDir);
        
        $this->setAbsoluteName($absoluteName);
        $this->noImage = $noImage;
        $this->basePath = trim($basePath, '/\\') . '/';
        
        $this->original = $this->createImageInfo(clone $this);
    }
    
    public function setNoImage($noImage) {
        $this->noImage = $noImage;
        
        return $this;
    }
    
    private function createNoImage() {
        $image = new self($this->assetsDir, $this->noImage, NULL, $this->basePath);
        
        $image->setWidth($this->getWidth());
        $image->setHeight($this->getHeight());
        $image->setIntegerFlag($this->getFlag());
        
        return $image;
    }
    
    public function exists() {
        return $this->createImageInfo($this)->isImageExists();
    }
    
    public function createLink() {
        $info = $this->createImageInfo($this);
        
        if (!$info->isImageExists() && !$this->original->isImageExists() && $this->noImage) {
            return $this->createNoImage()->createLink();
        }
        
        if (!$info->isImageExists() && $this->isResize()) {
            $image = $this->original->getImageClass();
            
            $info->createDirs();
            
            $image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
            
            $image->save($info->getAbsolutePath(), NULL, $this->original->getImageType());
            
            return $this->basePath . $info->getPath();
        } else if ($info->isImageExists()) {
            return $this->basePath . $info->getPath();
        } else if ($this->original->isImageExists()) {
            return $this->basePath . $this->original->getPath();
        }
    }
}
