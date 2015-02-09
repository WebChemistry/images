<?php

namespace WebChemistry\Images\Traits;

use Nette;

trait TGenerator {
    
    public function actionGenerate($name, $size = NULL, $flag = NULL, $noimage = NULL) {
        $info = $this->imageStorage->create($name, $size, $flag, $noimage)->createInfoLink(FALSE);
        
        if ($info->isImageExists()) {
            $image = Nette\Utils\Image::fromFile($info->getAbsolutePath());
            
            $info = getimagesize($info->getAbsolutePath());
            
            $image->send($info[2]);
        }
        
        $this->terminate();
    }
}
