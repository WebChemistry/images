<?php

namespace WebChemistry\Images\Traits;

use Nette;

trait TGenerator {
    
    public function actionGenerate($name, $size = NULL, $flag = NULL, $noimage = NULL) {
        $link = $this->imageStorage->create($name, $size, $flag, $noimage)->createLink();
        
        $absolute = $this->context->parameters['wwwDir'] . '/' . $link;
        
        if (file_exists($absolute)) {
            $image = Nette\Utils\Image::fromFile($absolute);
            
            $info = getimagesize($absolute);
            
            $image->send($info[2]);
        }
        
        $this->terminate();
    }
}
