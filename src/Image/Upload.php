<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Upload extends Creator {
    
    /** @var Nette\Http\FileUpload */
    protected $fileUpload;
    
    public function __construct($assetsDir, Nette\Http\FileUpload $fileUpload) {
        parent::__construct($assetsDir);
        
        if (!$fileUpload->isOk()) {
            throw new WebChemistry\Images\ImageStorageException('Uploaded file is invalid.');
        }
        
        $this->fileUpload = $fileUpload;
        
        $this->setName($fileUpload->getSanitizedName());
    }
    
    public function save() {
        $info = $this->getUniqueImage();
        $info->createDirs();
        
        $imageClass = $this->fileUpload->toImage();
        $this->processImage($imageClass);
        
        if ($this->callback) {
            $imageClass = call_user_func_array($this->callback, array($imageClass));
            
            if (!$imageClass instanceof Nette\Utils\Image) {
                throw new WebChemistry\Images\ImageStorageException('Callback must return Nette\Utils\Image.');
            }
        }
        
        $imageClass->save($info->getAbsolutePath(), $this->quality, $this->mimeToInteger($this->fileUpload->getContentType()));
        
        return $info;
    }
}
