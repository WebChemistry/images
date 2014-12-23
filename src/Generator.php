<?php

namespace WebChemistry\Images;

use Nette;

use Nette\Utils\Html;

/**
 * @property-read Root $parent
 */
class Generator extends Nette\ComponentModel\Component {
    
    /** @var string */
    private $basePath;
    
    /**
     * BasePath from Template
     * 
     * @param string $basePath
     * @return self
     */
    public function setBasePath($basePath) {
        $this->basePath = $basePath;
        
        return $this;
    }
    
    /**
     * @param string $name
     * @param array|string $sizes String = model's name
     * @param string|array $flags
     * @param array $attrs
     * @return Html
     */
    public function createResponsiveLinks($name, $sizes, $flags = NULL, array $attrs = []) {
        $container = Html::el('picture')->addAttributes(['data-settings' => json_encode($attrs)]);
        
        if (is_string($sizes)) {
            $sizes = $this->parent->getModel($sizes);
        }
        
        $i = 0;
        
        foreach ($sizes as $size => $media) {
            $i++;
            
            $source = Html::el('source')->src($this->basePath . $this->parent->storage->getImage($name, $size, $flags))->media($media);
            
            $container->add($source);
            
            if ($i === count($sizes)) {
                $container->add(Html::el('noscript')->add(Html::el('img')->src($this->basePath . $this->parent->storage->getImage($name, $size, $flags))->addAttributes($attrs)));
            }
        }
        
        return $container;
    }
    
    /**
     * @param string $name
     * @param string $size
     * @param string|array $flags
     * @return string
     */
    public function createLink($name, $size = NULL, $flags = NULL) {
        return $this->parent->storage->getImage($name, $size, $flags);
    }
}
