<?php

namespace WebChemistry\Images\Image;

use Nette;

class Info extends Nette\Object {
    
    const ORIGINAL = 'original';
    
    const PREFIX_SEP = '_._';
    
    private $assetsDir;
    
    /** @var Container */
    private $image;
    
    private $imageSize;
    
    public function __construct(IImage $image, $assetsDir) {
        $this->assetsDir = $assetsDir;
        $this->image = $image;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    protected function getImageSizeInfo() {
        if (!$this->imageSize) {
            $this->imageSize = getimagesize($this->getAbsolutePath());
        }
        
        return $this->imageSize;
    }
    
    public function getImageType() {
        $info = $this->getImageSizeInfo();
        
        return $info[2];
    }
    
    public function getImageClass() {
        return Nette\Utils\Image::fromFile($this->getAbsolutePath());
    }
    
    public function getPath() {
        return $this->namespaceFolder() . $this->baseFolder() . $this->getNameWithPrefix();
    }
    
    public function getAbsolutePath() {
        return $this->assetsDir . $this->getPath();
    }
    
    public function getAbsoluteNamespace() {
        return $this->assetsDir . $this->namespaceFolder();
    }
    
    public function getAbsoluteName() {
        return $this->namespaceFolder() . $this->getNameWithPrefix();
    }
    
    public function isImageExists() {
        return file_exists($this->getAbsolutePath());
    }
    
    public function createDirs() {
        if ($this->namespaceFolder()) {
            $this->createNamespaceFolders();
        }
        
        @mkdir($this->assetsDir . $this->namespaceFolder() . $this->baseFolder()); // Original | resize dir
    }
    
    private function createNamespaceFolders() {
        $lastDir = $this->assetsDir;
        
        foreach (explode('/', $this->image->getNamespace()) as $namespace) {
            $lastDir .= $namespace . '/';
            
            @mkdir($lastDir);
        }
    }
    
    private function namespaceFolder() {
        if ($this->image->getNamespace()) {
            return $this->image->getNamespace() . '/';
        }
    }
    
    private function getNameWithPrefix() {
        $prefix = $this->image->getPrefix();
        
        return ($prefix ? $prefix . self::PREFIX_SEP : NULL) . $this->image->getName();
    }
    
    private function baseFolder() {
        return $this->sizeFolder() . $this->flagFolder() . '/';
    } 
    
    private function sizeFolder() {
        if ($this->image->getCrop()) {
            return 'crop-' . implode('x', $this->image->getCrop());
        }
        
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();
        
        if ($width && $height) {
            return $width . 'x' . $height;
        } else if ($width)  {
            return $width . 'x';
        } else if ($height) {
            return 'x' . $height;
        }
        
        return self::ORIGINAL;
    }
    
    private function flagFolder() {
        if ($this->image->getFlag()) {
            return '_' . $this->image->getFlag();
        }
    }
    
    public function __toString() {
        return $this->getAbsoluteName();
    }
}
