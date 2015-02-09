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
    
    public function createInfoLink($createResized = TRUE) {
        $info = $this->createImageInfo($this);
        
        if (!$info->isImageExists() && !$this->original->isImageExists() && $this->noImage) {
            return $this->createNoImage()->createInfoLink();
        }
        
        if (!$info->isImageExists() && $this->isResize() && $createResized) {
            $image = $this->original->getImageClass();
            
            $info->createDirs();
            
            $image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
            
            $image->save($info->getAbsolutePath(), NULL, $this->original->getImageType());

            return $info;
        } else if ($info->isImageExists()) {
            return $info;
        } else if ($this->original->isImageExists()) {
            return $this->original;
        }
        
        return $info; // Disallow re-loading page as image
    }
    
    public function createLink($createResized = TRUE) {
        $info = $this->createImageInfo($this);
        
        if (!$info->isImageExists() && !$this->original->isImageExists() && $this->noImage) {
            return $this->createNoImage()->createLink();
        }
        
        if (!$info->isImageExists() && $this->isResize() && $createResized) {
            $image = $this->original->getImageClass();
            
            $info->createDirs();
            
            $image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
            
            $image->save($info->getAbsolutePath(), NULL, $this->original->getImageType());

            return str_replace('%', '%25', $this->basePath . $info->getPath());
        } else if ($info->isImageExists()) {
            return str_replace('%', '%25', $this->basePath . $info->getPath());
        } else if ($this->original->isImageExists()) {
            return $this->basePath . $this->original->getPath();
        }
        
        return $this->basePath . $info->getPath(); // Disallow re-loading page as image
    }
}
