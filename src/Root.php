<?php

namespace WebChemistry\Images;

use Nette;

/**
 * @property-read Storage $storage
 * @property-read Geneartor $generator
 * @property-read Helpers\Helper $helper
 * @property-read Helpers\Directory $directory
 * @property string $original Original directory
 * @property string $noImage
 * @property string $wwwDir
 * @property string $imageDir
 */
class Root extends Nette\ComponentModel\Container {
    
    /** @vat string */
    protected $imageDir;
    
    /** @var string */
    protected $original;
    
    /** @var string */
    protected $wwwDir;
    
    /** @var array */
    protected $models;
    
    /** @var array */
    protected $settings;
    
    public function __construct($wwwDir, array $settings = []) {
        $this->wwwDir = $wwwDir;
        $this->settings = $settings;
        
        $this->addComponent(new Storage, 'storage');
        $this->addComponent(new Helpers\Helper, 'helper');
        $this->addComponent(new Generator, 'generator');
        $this->addComponent(new Helpers\Directory, 'directory');
    }
    
    /**
     * @return Helpers\Helper
     */
    public function getHelper() {
        return $this->getComponent('helper');
    }
    
    /**
     * @return array
     */
    public function getSettings() {
        return $this->settings;
    }
    
    /**
     * @return Generator
     */
    public function getGenerator() {
        return $this->getComponent('generator');
    }
    
    /**
     * @return Storage
     */
    public function getStorage() {
        return $this->getComponent('storage');
    }
    
    /**
     * @return Helpers\Directory
     */
    public function getDirectory() {
        return $this->getComponent('directory');
    }
    
    /**
     * @param string $model
     * @return array
     */
    public function getModel($model) {
        return $this->models[$model];
    }
    
    /**
     * @param array $models
     * @return self
     */
    public function setModels($models) {
        $this->models = $models;
        
        return $this;
    }
    
    /**
     * @param string $wwwDir
     * @return self
     */
    public function setWwwDir($wwwDir) {
        if (!Strings::endsWith($wwwDir, '/') && Strings::endsWith($wwwDir, '\\')) {
            $wwwDir .= '/';
        }
        
        $this->wwwDir = $wwwDir;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getWwwDir() {
        return $this->wwwDir;
    }
    
    /**
     * @param string $imageDir
     * @return self
     * @throws ImageStorageException
     */
    public function setImageDir($imageDir) {
        $this->imageDir = $this->helper->checkName($imageDir);
        
        if (!$this->imageDir) {
            throw new ImageStorageException('Image dir must not be empty.');
        }
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getImageDir() {
        return $this->imageDir;
    }
    
    /**
     * @return string
     */
    public function getOriginal() {
        return $this->original;
    }
    
    /**
     * @param string $original
     * @return self
     */
    public function setOriginal($original) {
        $this->original = $original;
        
        return $this;
    }
    
    /**
     * @param string $image
     */
    public function setNoImage($image) {
        $this->noImage = $image;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getNoImage() {
        return $this->noImage;
    }
}

class ImageStorageException extends \Exception {}
