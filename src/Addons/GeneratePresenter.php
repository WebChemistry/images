<?php

namespace WebChemistry\Images\Addons;

use Nette, WebChemistry, Tracy;

class GeneratePresenter extends Nette\Application\UI\Presenter {
    
    use WebChemistry\Images\Traits\TPresenter;
    
    /** @var bool */
    private $resize = FALSE;
    
    public function __construct($resize = FALSE) {
        $this->resize = (bool) $resize;
    }
    
    public function actionDefault($name, $size = NULL, $flag = NULL, $noimage = NULL) {
        try {
            $info = $this->imageStorage->create($name, $size, $flag, $noimage)->createInfoLink($this->resize);
        } catch (WebChemistry\Images\ImageStorageException $e) {
            if (Tracy\Debugger::isEnabled()) {
                throw $e;
            } else {
                $this->getHttpResponse()->setCode(404);
                
                $this->terminate();
            }
        }
        
        if ($info->isImageExists()) {
            $image = Nette\Utils\Image::fromFile($info->getAbsolutePath());
            
            $info = getimagesize($info->getAbsolutePath());
            
            $image->send($info[2]);
        } else {
            $this->getHttpResponse()->setCode(404);
                
            $this->terminate();
        }
    }
}
