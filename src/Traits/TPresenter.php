<?php

namespace WebChemistry\Images;

trait TPresenter {
    
    /** @var \WebChemistry\Images\Root */
    private $_imageRoot;
    
    /** @var \WebChemistry\Images\Storage */
    protected $imageStorage;
    
    public function injectImage(\WebChemistry\Images\Root $root) {
        $this->_imageRoot = $root;
        $this->imageStorage = $root->storage;
    }
    
    public function createTemplate() {
        $template = parent::createTemplate();
        
        $template->_image = $this->_imageRoot->generator;
        
        return $template;
    }
}
