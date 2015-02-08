<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Delete extends Container {
    
    private $info;
    
    public function __construct($assetsDir) {
        parent::__construct($assetsDir);
        
        $this->info = $this->createImageInfo($this);
    }
    
    public function getNamespacePath() {
        return $this->info->getAbsoluteNamespace();
    }
}
