<?php

namespace WebChemistry\Images;

class NamespaceStorage extends Storage {
    
    public function __construct($assetsDir, $basePath, $noImage, $settings, $namespace = NULL) {
        parent::__construct($assetsDir, $basePath, $noImage, $settings);
        
        $this->namespace = $namespace;
    }
}
