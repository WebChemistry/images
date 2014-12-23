<?php

namespace WebChemistry\Images;

use Nette,
    Nette\Utils\Strings,
    Nette\Utils\Random,
    Nette\Utils\Image as NImage,
    Nette\Utils\Finder;

/**
 * @property-read Root $parent
 */
class Storage extends Nette\ComponentModel\Component {
    
    /** @var string */
    protected $namespace;
    
    /**
     * @param string $namespace
     * @return self
     */
    public function setNamespace($namespace) {
        $this->namespace = $this->parent->helper->checkName($namespace);
        
        return $this;
    }
    
    /**
     * @param string $filename
     * @return int
     */
    public function delete($filename) {
        list($namespace, $filename) = $this->parent->helper->name($filename);
        
        $files = Finder::find($filename)->from($this->parent->wwwDir . $this->parent->directory->getBase($namespace));
        $count = 0;
        
        foreach ($files as $path) {
            if (@unlink($path)) {
                $count++;
            }
        }
        
        $this->namespace = NULL;
        
        return $count;
    }
    
    /**
     * @param Nette\Http\FileUpload $upload
     * @return boolean|Image
     */
    public function saveUpload(Nette\Http\FileUpload $upload) {
        if (!$upload->isOk()) {
            return FALSE;
        }
        
        $this->createDirectories();
        
        $path = $this->parent->wwwDir . $this->parent->directory->getOriginal($this->namespace) . '/';
        $sanitizedName = $name = $upload->getSanitizedName();
        
        if (file_exists($fullPath = $path . $name)) {
            while (file_exists($fullPath = $path . $name)) {
                $name = Random::generate() . '.' . $sanitizedName;
            }
        }
        
        $upload->move($fullPath);
        $image = new Image($this->namespace, $name, $fullPath);
        $this->namespace = NULL;
        
        return $image;
    }
    
    public function createDirectories() {
        @mkdir($this->parent->wwwDir . $this->parent->directory->getBase($this->namespace));
        @mkdir($this->parent->wwwDir . $this->parent->directory->getOriginal($this->namespace));
    }
    
    /**
     * @param string $content
     * @param string $filename
     * @return type
     */
    public function saveContent($content, $filename) {
        return $this->saveImage(NImage::fromString($content), $filename);
    }
    
    /**
     * @param NImage $image
     * @param string $filename
     * @return Image
     */
    public function saveImage(NImage $image, $filename) {
        $this->createDirectories();
        
        $path = $this->parent->wwwDir . $this->parent->directory->getOriginal($this->namespace) . '/';
        
        $name = $filename;
        
        if (file_exists($fullPath = $path . $filename)) {
            while (file_exists($fullPath = $path . $name )) {
                $name = Random::generate() . '.' . $name;
            }
        }
        
        $image->save($fullPath);
        $return = new Image($this->namespace, $name, $fullPath);
        $this->namespace = NULL;
        
        return $return;
    }
    
    /**
     * @param string $name
     * @param string|array $size
     * @param string|array $flags
     * @return string
     */
    public function getAbsoluteImage($name, $size = NULL, $flags = NULL) {
        return $this->parent->wwwDir . $this->getImage($name, $size, $flags);
    }
    
    /**
     * @param string $name
     * @param string|array $size
     * @param string|array $flags
     * @return string
     */
    public function getImage($name, $size = NULL, $flags = NULL) {
        if (!$name || !strpos($name, '.')) {
            return $this->parent->directory->getNoImage();
        }
        
        list($namespace, $filename) = $this->parent->helper->name($name);
        $sizes = $this->parent->helper->size($size);
        $flag = $this->parent->helper->flags($flags);
        $wwwDir = $this->parent->wwwDir;
        
        $current = $this->parent->directory->getFull($namespace, $sizes, $flag) . '/' . $filename;
        
        if (!file_exists($wwwDir . $current)) {
            $original = $this->parent->directory->getOriginal($namespace) . '/' . $filename;
            
            if (!file_exists($wwwDir . $original)) {
                return $this->parent->directory->getNoImage();
            }
            
            $this->createResized($this->parent->directory->getFull($namespace, $sizes, $flag), $current, $original, $sizes, $flag);
        }
        
        return $current;
    }
    
    /**
     * @param string $resizedDir
     * @param string $resized
     * @param string $original
     * @param array $sizes
     * @param int $flag
     */
    public function createResized($resizedDir, $resized, $original, $sizes, $flag) {
        $wwwDir = $this->parent->wwwDir;
        
        @mkdir($wwwDir . $resizedDir);
        
        $image = NImage::fromFile($wwwDir . $original);
        
        $image->resize($sizes[0], $sizes[1], $flag);
        
        $image->save($wwwDir . $resized);
    }
}
