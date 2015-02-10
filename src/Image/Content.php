<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Content extends Creator {
    
    /** @var string */
    protected $content;
    
    public function __construct($assetsDir, $content) {
        parent::__construct($assetsDir);
        
        if (!is_string($content)) {
            throw new WebChemistry\Images\ImageStorageException('Content of image must be string.');
        }
        
        $this->content = $content;
    }
    
    protected function getImageType() {
        $fileInfo = finfo_open();
        
        return $this->mimeToInteger(finfo_buffer($fileInfo, $this->content, FILEINFO_MIME_TYPE));
    }
    
    public function save() {
        if (!$this->getName()) {
            throw new WebChemistry\Images\ImageStorageException('Image name must be set.');
        }
        
        $info = $this->getUniqueImage();
        $info->createDirs();
        
        $imageClass = Nette\Utils\Image::fromString($this->content);
        $this->processImage($imageClass);
        
        if ($this->callback) {
            $imageClass = call_user_func_array($this->callback, array($imageClass));
            
            if (!$imageClass instanceof Nette\Utils\Image) {
                throw new WebChemistry\Images\ImageStorageException('Callback must return Nette\Utils\Image.');
            }
        }
        
        $imageClass->save($info->getAbsolutePath(), $this->quality, $this->getImageType());
        
        return $info;
    }
}
