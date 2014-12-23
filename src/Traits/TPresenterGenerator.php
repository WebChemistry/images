<?php

namespace WebChemistry\Images;

trait TPresenterGenerator {
    
    public function actionImage($image, $size = NULL, $flags = NULL) {
        $image = \Nette\Utils\Image::fromFile($this->imageStorage->getAbsoluteImage($image, $size, $flags));
        
        $image->send();
    }
}
