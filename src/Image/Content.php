<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Content extends Container {
    
    /** @var string */
    protected $content;
    
    public function __construct($assetsDir, $content) {
        parent::__construct($assetsDir);
        
        if (!is_string($content)) {
            throw new WebChemistry\Images\ImageStorageException('Content of image must be string.');
        }
        
        $this->content = $content;
    }
    
    public function save() {
        if (!$this->getName()) {
            throw new WebChemistry\Images\ImageStorageException('Image name must be set.');
        }
        
        $image = $this->getUniqueImage();
        
        $open = fopen($image->getAbsolutePath(), 'w');
        fwrite($open, $this->content);
        fclose($open);
        
        return $image;
    }
}
