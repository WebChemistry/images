<?php

namespace WebChemistry\Images\Image;

use Nette;

use WebChemistry;

class Creator extends Container {
    
    protected $callback;
    
    protected $quality;
    
    public function setQuality($quality) {
        if (!is_int($quality)) {
            throw new WebChemistry\Images\ImageStorageException('Quality must be integer.');
        }
        
        $this->quality = $quality;
        
        return $this;
    }
    
    public function getQuality() {
        return $this->quality;
    }
    
    protected function processImage(Nette\Utils\Image $image) {
        if ($this->getWidth() || $this->getHeight()) {
            $image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
        }
        
        return $image;
    }
    
    public function setCallback($callback) {
        Nette\Utils\Callback::check($callback);
        
        $this->callback = $callback;
        
        return $this;
    }
    
    public function getImageClass() {
        throw new WebChemistry\Images\ImageStorageException('You cannot get image class, please use callback.');
    }
    
    protected function mimeToInteger($mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return Nette\Utils\Image::JPEG;
                break;
            case 'image/png':
                return Nette\Utils\Image::PNG;
                break;
            case 'image/gif':
                return Nette\Utils\Image::GIF;
        }
        
        return $mimeType;
    }
    
    public function createImageInfo(IImage $class = NULL) {
        // Disallow create image to "resized" directory
        $info = clone $this;
        $info->setWidth(NULL);
        $info->setHeight(NULL);
        $info->setFlag('fit');
        
        return parent::createImageInfo($info); 
    }
}
