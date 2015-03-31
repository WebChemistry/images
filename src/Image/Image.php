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
    
    private function creator($createResized = TRUE, $createInfo = FALSE) {
        $info = $this->createImageInfo($this);
        if (!$info->isImageExists() && !$this->original->isImageExists() && $this->noImage) {
            return $this->createNoImage()->createLink();
        }
        
        if (!$info->isImageExists() && $this->isResize() && $createResized) {
            $image = $this->original->getImageClass();
            
            $info->createDirs();
            
            if ($this->getCrop()) {
                call_user_func_array(array($image, 'crop'), $this->getCrop());
            } else if ($this->getWidth() || $this->getHeight()) {
                $image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
            }
            $image->save($info->getAbsolutePath(), $this->getQuality(), $this->original->getImageType());
            
            return $createInfo ? $info : str_replace('%', '%25', $this->basePath . $info->getPath());
        } else if ($info->isImageExists()) {
            return $createInfo ? $info : str_replace('%', '%25', $this->basePath . $info->getPath());
        } else if ($this->original->isImageExists()) {
            return $createInfo ? $this->original : $this->basePath . $this->original->getPath();
        }
        
        return $createInfo ? $info : $this->basePath . $info->getPath(); // Disallow re-loading page as image
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
        return $this->creator($createResized, TRUE);
    }
    
    public function createLink($createResized = TRUE) {
        return $this->creator($createResized);
    }
}
