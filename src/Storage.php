<?php

namespace WebChemistry\Images;

use Nette;

class Storage extends Nette\Object {
    
    protected $assetsDir;
    
    protected $basePath;
    
    protected $noImage;
    
    protected $settings;
    
    protected $namespace;
    
    public function __construct($assetsDir, $basePath, $noImage, $settings) {
        $this->assetsDir = $assetsDir;
        $this->noImage = $noImage;
        $this->basePath = $basePath;
        $this->settings = $settings;
    }
    
    public function createNamespace($namespace) {
        return new NamespaceStorage($this->assetsDir, $this->basePath, $this->noImage, $this->settings, $namespace);
    }
    
    public function getSettings() {
        return $this->settings;
    }
    
    public function fromUpload(Nette\Http\FileUpload $upload, $namespace = NULL) {
        if (!$upload->isOk()) {
            return FALSE;
        }
        
        $image = new Image\Upload($this->assetsDir, $upload);
        
        if ($namespace) {
            $image->setNamespace($namespace);
        } else if ($this->namespace) {
            $image->setNamespace($this->namespace);
        }
        
        return $image;
    }
    
    public function saveUpload(Nette\Http\FileUpload $upload, $namespace = NULL) {
        return $this->fromUpload($upload, $namespace)->save();
    }
    
    public function fromContent($content, $name, $namespace = NULL) {
        $image = new Image\Content($this->assetsDir, $content);
        
        $image->setName($name);
        
        if ($namespace) {
            $image->setNamespace($namespace);
        } else if ($this->namespace) {
            $image->setNamespace($this->namespace);
        }
        
        return $image;
    }
    
    public function saveContent($content, $name, $namespace = NULL) {
        return $this->fromContent($content, $name, $namespace)->save();
    }
    
    public function create($absoluteName, $size = NULL, $flag = NULL, $noImage = NULL) {
        $image = new Image\Image($this->assetsDir, $absoluteName, $noImage ? $noImage : $this->noImage, $this->basePath);
        
        $image->setSize($size);
        $image->setFlag($flag);
        
        return $image;
    }
    
    public function delete($absoluteName) {
        if (!is_string($absoluteName) || !$absoluteName) {
            return;
        }
        
        $image = new Image\Delete($this->assetsDir);
        
        if ($this->namespace && strpos('/', $absoluteName) === FALSE) {
            $image->setName($absoluteName);
        } else {
            $image->setAbsoluteName($absoluteName);
        }
        
        if (!file_exists($image->getNamespacePath())) {
            return;
        }
        
        $finder = Nette\Utils\Finder::findFiles($image->getName())
                        ->from($image->getNamespacePath())
                        ->limitDepth(1);
        
        foreach ($finder as $row) {
            @unlink((string) $row);
        }
    }
}
