<?php

namespace WebChemistry\Images\Addons;

use Nette,
        Nette\Utils\Html;

use WebChemistry;

class UploadControl extends Nette\Forms\Controls\UploadControl {
    
    const CHECKBOX_NAME = 'checkbox_image_delete';
    
    /** @var string|null */
    private $default = NULL;
    
    /** @var string|null */
    private $namespace = NULL;
    
    /** @var boolean */
    private $preview = TRUE;
    
    /** @var string */
    private $imageName;
    
    /** @var boolean */
    private $isDelete = FALSE;

    /**
     * @param string|null $label
     * @param string|null $namespace
     * @param boolean $multiple
     */
    public function __construct($label = NULL, $namespace = NULL, $defaultValue = NULL, $multiple = FALSE) {
        parent::__construct($label, $multiple);
        
        $this->namespace = $namespace;
        $this->defaultValue = $defaultValue;
        
        $this->addCondition(Nette\Application\UI\Form::FILLED)->addRule(Nette\Application\UI\Form::IMAGE)->endCondition();
        
        $this->monitor('Nette\Application\IPresenter');
    }
    
    
    protected function attached($form) {
        if ($form instanceof Nette\Forms\Form) {
            $form->onError[] = $this->cleanUp;
            $form->onSuccess[] = $this->delete;
        }
        
        parent::attached($form);
    }
    
    public function delete($form) {
        if ($this->isDelete) {
            $this->getStorage()->delete($this->default);
        }
    }
    
    public function cleanUp($form) {
        if ($this->imageName) {
            $this->getStorage()->delete((string) $this->imageName->getAbsoluteName());
        }
    }
    
    public static function validateImage(Nette\Forms\Controls\UploadControl $control) {
        return is_string($control->getValue()) || $control->default;
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

        if ((bool) $checkbox === TRUE) {
            // If checkbox was send
            $this->isDelete = TRUE;
            
            return NULL;
        } else if ($this->default) {
            // Against multiupload same image
            $exists = $this->getStorage()->create($this->default)->exists();
            
            if ($exists) {
                return $this->default;
            }
        }
        // Uploading
        $upload = $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
        
        if ($upload && $upload->isOk() && $upload->isImage()) {
            $image = $this->getStorage()->saveUpload($upload, $this->namespace);
            
            return (string) $this->imageName = $image;
        }
        
        // Not uploaded && image does not exist
        return NULL;
    }
    
    public function loadHttpData() {
        $this->value = $this->getHttpData(Nette\Forms\Form::DATA_FILE);
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
    
    public function setValue($value) {
        $this->default = $value;
        
        return $this;
    }
    
    /**
     * @return WebChemistry\Images\Storage
     */
    private function getStorage() {
        return $this->lookup('Nette\Application\IPresenter')->context->getByType('WebChemistry\Images\Storage');
    }
    
    /**
     * Settings for this addon
     * 
     * @return array
     */
    private function getSettings() {
        return $this->getStorage()->settings['upload'];
    }
    
    public function getLabel($caption = NULL) {
        $exists = $this->getStorage()->create($this->default)->exists();
        
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
        $image = Html::el('img')->setClass('upload-preview-image')->setSrc($this->lookup('Nette\Application\IPresenter')->getTemplate()->basePath . '/' . $this->getStorage()->create($this->default)->createLink());
        
        return Html::el('div')->setClass('upload-preview-image-container')->add($image);
    }
    
    /**
     * Return checkbox or upload control.
     * 
     * @return Html
     */
    public function getControl() {
        $exists = $this->getStorage()->create($this->default)->exists();
        
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
    
    
    public static function register($controlName = 'addImageUpload') {
        if (!is_string($controlName)) {
            throw new WebChemistry\Images\ImageStorageException(sprintf('Control name must be string, %s given', gettype($controlName)));
        }
        
        Nette\Object::extensionMethod('Nette\Forms\Container::addImageUpload', function ($form, $name, $label = NULL, $namespace = NULL, $defaultValue = NULL) {
            return $form[$name] = new WebChemistry\Images\Addons\UploadControl($label, $namespace, $defaultValue);
        });
    }
}