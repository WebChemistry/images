<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Upload extends Container {
    
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
    
    public function getImageClass() {
        return $this->fileUpload->toImage();
    }
    
    public function save() {
        $image = $this->getUniqueImage();
        
        $image->createDirs();
        
        $this->fileUpload->move($image->getAbsolutePath());
        
        return $image;
    }
}
