<?php

namespace WebChemistry\Images\Addons;

use Nette,
        Nette\Utils\Html;

class UploadControl extends Nette\Forms\Controls\UploadControl {
    
    const CHECKBOX_NAME = 'checkbox_image_delete';
    
    /** @var string|null */
    private $default = NULL;
    
    /** @var string|null */
    private $namespace = NULL;
    
    /** @var boolean */
    private $preview = TRUE;

    /**
     * @param string|null $label
     * @param string|null $namespace
     * @param boolean $multiple
     */
    public function __construct($label = NULL, $namespace = NULL, $multiple = FALSE) {
        parent::__construct($label, $multiple);
        
        $this->namespace = $namespace;
        
        $this->addCondition(Nette\Application\UI\Form::FILLED)->addRule(Nette\Application\UI\Form::IMAGE)->endCondition();
        $this->monitor('Nette\Application\IPresenter');
    }
    
    public static function validateImage(Nette\Forms\Controls\UploadControl $control) {
        return is_string($control->getValue()) || $control->default;
        
        if (is_string($control->getValue())) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * @param string|null $namespace
     * @return self
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        
        return $this;
    }
    
    /**
     * If want preview of picture in form
     * 
     * @param boolean $preview
     * @return self
     */
    public function setPreview($preview) {
        $this->preview = (bool) $preview;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getHttpData($type, $htmlTail = NULL) {
        $checkbox = $this->getForm()->getHttpData(Nette\Application\UI\Form::DATA_LINE, self::CHECKBOX_NAME);

        if ($checkbox) {
            return (bool) $checkbox;
        }
        
        return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
    }
    
    /**
     * Shortname of image
     * 
     * @param string|null $value
     * @return self
     */
    public function setDefaultValue($value) {
        $this->default = $value;
        
        return $this;
    }
    
    /**
     * @return \WebChemistry\Images\Root
     */
    private function getRoot() {
        return $this->lookup('Nette\Application\IPresenter')->context->getByType('WebChemistry\Images\Root');
    }
    
    /**
     * Settings for this addon
     * 
     * @return array
     */
    private function getSettings() {
        return $this->getRoot()->settings['upload'];
    }
    
    /**
     * @param string $value
     * @return self
     */
    public function setValue($value) {
        $this->default = $value;
        
        return $this;
    }
    
    /**
     * False = Upload failing. NULL = Successfull delete. String = Name of image 
     * 
     * @return null|string
     */
    public function getValue() {
        $value = parent::getValue();
        
        $storage = $this->getRoot()->getStorage();
        
        if ($value === TRUE) {
            $storage->delete($this->default);
            
            return NULL;
        } else if ($value->isOk() && $value->isImage()) {
            $storage->setNamespace($this->namespace);
            
            $image = $storage->saveUpload($value);
            
            return (string) $image;
        }
        
        return $this->default ? $this->default : NULL;
    }
    
    public function getLabel($caption = NULL) {
        $exists = $this->getRoot()->getStorage()->exists($this->default);
        
        if ($this->default && $exists) {
            return NULL;
        }
        
        return parent::getLabel($caption);
    }
    
    /**
     * Preview of image
     * 
     * @return Html
     */
    public function getImage() {
        $image = Html::el('img')->setClass('upload-preview-image')->setSrc($this->lookup('Nette\Application\IPresenter')->getTemplate()->basePath . $this->getRoot()->getStorage()->getImage($this->default));
        
        return Html::el('div')->setClass('upload-preview-image-container')->add($image);
    }
    
    /**
     * Return checkbox or upload control.
     * 
     * @return Html
     */
    public function getControl() {
        $exists = $this->getRoot()->getStorage()->exists($this->default);
        
        if ($this->default && $exists === TRUE) {
            $settings = $this->getSettings();
            
            $control = new Nette\Forms\Controls\Checkbox($settings['label']);
            
            $control->setParent($this->getForm(), 'checkbox_image_delete');
            
            $container = Html::el();
            
            $container->add($control->getControl());
            
            if ($this->preview) {
                $container->add($this->getImage());
            }
            
            return $container;
        }
        
        return parent::getControl();
    }
    
    
    public static function register() {
        Nette\Application\UI\Form::extensionMethod('addImageUpload', function ($form, $name, $label = NULL, $multiple = FALSE) {
            return $form[$name] = new self($label, FALSE);
        });
    }
}