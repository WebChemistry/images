<?php

namespace WebChemistry\Images\Helpers;

use Nette, WebChemistry;

/**
 * @property-read WebChemistry\Images\Root $parent
 * @property-read string $noImage
 */
class Directory extends Nette\ComponentModel\Component {
    
    /**
     * @param string|null $namespace
     * @return string
     */
    public function getBase($namespace = NULL) {
        return '/' . $this->parent->imageDir . ($namespace ? '/' . $this->parent->helper->checkName($namespace) : '');
    }
    
    /**
     * @param string|null $namespace
     * @param string|array $size
     * @param int $flag
     * @return string
     */
    public function getFull($namespace = NULL, $size = NULL, $flag = NULL) {
        return $this->getBase($namespace) . '/' . $this->parent->helper->resizedImageDir($size, $flag);
    }
    
    /**
     * @param string|null $namespace
     * @return string
     */
    public function getOriginal($namespace = NULL) {
        return $this->getFull($namespace);
    }
    
    /**
     * @return string
     */
    public function getNoImage() {
        return $this->getBase() . '/' . $this->parent->noImage;
    }
}
