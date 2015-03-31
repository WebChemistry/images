<?php

namespace WebChemistry\Images\Addons;

use Nette,
        Nette\Utils\Html;

use WebChemistry;

class UploadControl extends Nette\Forms\Controls\UploadControl {
    
    protected $checkboxName = 'checkbox_image_delete';
    
    /** @var string|null */
    protected $default = NULL;
    
    /** @var string|null */
    protected $namespace = NULL;
    
    /** @var boolean */
    protected $preview = TRUE;
    
    /** @var array */
    protected $imageNames = array();
    
    /** @var boolean */
    protected $isDelete = FALSE;
    
    protected $rawValue = array();

    protected $isHttpData = FALSE;
    
    /** @var boolean */
    protected $multiple = FALSE;
    
    /**
     * @param string|null $label
     * @param string|null $namespace
     * @param boolean $multiple
     */
    public function __construct($label = NULL, $namespace = NULL, $defaultValue = NULL) {
        parent::__construct($label, FALSE);
        
        $this->namespace = $namespace;
        $this->defaultValue = $defaultValue;
        
        $this->addCondition(Nette\Forms\Form::FILLED)->addRule(Nette\Forms\Form::IMAGE)->endCondition();
        
        $this->monitor('Nette\Application\IPresenter');
    }
    
    protected function attached($form) {
        if ($form instanceof Nette\Forms\Form) {
            $form->onError[] = $this->cleanUp;
            $form->onSuccess[] = $this->delete;
        }
        
        parent::attached($form);
    }
    
    public function isUpload() {
        return is_array($this->rawValue);
    }
    
    public function delete($form) {
        if ($this->isDelete) {
            $this->getStorage()->delete($this->default);
        }
    }
    
    public function cleanUp($form) {
        if ($this->imageNames) {
            foreach ($this->imageNames as $name) {
                $this->getStorage()->delete($name);
            }
        }
    }
    
    public function isFilled() {
        if ($this->isUpload()) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function validate() {
        foreach ($this->rules as $rule) {
            $this->adjustRule($rule);
        }
        parent::validate();
    }
    
    /**
     * Change callbacks from default validator to this class
     * 
     * @param Nette\Forms\Rule $rule
     */
    private function adjustRule(Nette\Forms\Rule $rule) {
        switch ($rule->validator) {
            case Nette\Forms\Form::IMAGE:
                $rule->validator = get_class($this) . '::validateImage';
                if (!$rule->message) {
                    $rule->message = Nette\Forms\Validator::$messages[Nette\Forms\Form::IMAGE];
                }
                break;
            case Nette\Forms\Form::MAX_FILE_SIZE:
                $rule->validator = get_class($this) . '::validateFileSize';
                if (!$rule->message) {
                    $rule->message = Nette\Forms\Validator::$messages[Nette\Forms\Form::MAX_FILE_SIZE];
                }
                break;
            case Nette\Forms\Form::MIME_TYPE:
                $rule->validator = get_class($this) . '::validateMimeType';
                if (!$rule->message) {
                    $rule->message = Nette\Forms\Validator::$messages[Nette\Forms\Form::MIME_TYPE];
                }
                break;
        }
        
        if ($rule->branch) {
            foreach ($rule->branch as $branch) {
                $this->adjustRule($branch); 
            }
        }
    }
    
    public static function validateImage(Nette\Forms\Controls\UploadControl $control, $message = NULL) {
        if ($control->isUpload()) {
            foreach ($control->rawValue as $upload) {
                if (!$upload->isImage()) {
                    return FALSE;
                }
            }
        }
        
        return TRUE;
    }
    
    public static function validateFileSize(Nette\Forms\Controls\UploadControl $control, $limit) {
        if ($control->isUpload()) {
            foreach ($control->rawValue as $upload) {
                if ($upload->getSize() > $limit || $upload->getError() === UPLOAD_ERR_INI_SIZE) {
                    return FALSE;
                }
            }
        }
        
        return TRUE;
    }
    
    public static function validateMimeType(Nette\Forms\Controls\UploadControl $control, $mimeType) {
        throw new WebChemistry\Images\ImageStorageException('You cannot set mime type.');
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
    
    protected function getCheckboxName($getter = FALSE) {
        if ($getter === TRUE && !$this->parent instanceof Nette\Forms\Form) {
            return \Nette\Forms\Helpers::generateHtmlName($this->parent->lookupPath('Nette\Forms\Form') . '-' . $this->getCheckboxName());
        }
        
        return $this->name . '_' . $this->checkboxName;
    }
    
    public function getRawValue() {
        return $this->rawValue;
    }
    
    /**
     * @return mixed
     */
    public function getHttpData($type, $htmlTail = NULL) {
        if ($this->isHttpData) {
            return $this->value;
        }
        
        $this->isHttpData = TRUE;
        
        $checkbox = $this->getForm()->getHttpData(Nette\Application\UI\Form::DATA_LINE, $this->getCheckboxName(TRUE));
        
        if ((bool) $checkbox === TRUE) {
            // If checkbox was send
            $this->isDelete = TRUE;
            
            $this->rawValue = TRUE;
            
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
        
        return $this->saveUpload($upload);
    }
    
    protected function saveUpload($upload) {
        $return = array();
        $this->rawValue = array();
        
        foreach ($this->uploadArray($upload) as $file) {
            if ($file && $file->isOk() && $file->isImage()) {
                $this->rawValue[] = $file;
                $image = $this->getStorage()->saveUpload($file, $this->namespace);
                
                $return[] = $this->imageNames[] = (string) $image;
            }
        }
        
        if (!$this->rawValue) {
            $this->rawValue = NULL;
        }
        
        return $this->getUploadValue($return);
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
    protected function getStorage() {
        return $this->lookup('Nette\Application\IPresenter')->context->getByType('WebChemistry\Images\Storage');
    }
    
    /**
     * Settings for this addon
     * 
     * @return array
     */
    protected function getSettings() {
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
        if ($this->useCheckbox()) {
            $settings = $this->getSettings();
            
            $control = new Nette\Forms\Controls\Checkbox($settings['label']);
            
            $control->setHtmlId($this->getHtmlId());
            $control->setParent($this->lookup('Nette\Forms\Container'), $this->getCheckboxName());
            
            $container = Html::el();
            
            $container->add($control->getControl());
            
            if ($this->preview) {
                $container->add($this->getImage());
            }
            
            return $container;
        }
        
        return parent::getControl();
    }
    
    public function useCheckbox() {
        return $this->default && $this->getStorage()->create($this->default)->exists() === TRUE;
    }
    
    public static function register($controlName = 'addImageUpload') {
        if (!is_string($controlName)) {
            throw new WebChemistry\Images\ImageStorageException(sprintf('Control name must be string, %s given', gettype($controlName)));
        }
        
        Nette\Object::extensionMethod('Nette\Forms\Container::' . $controlName, function ($form, $name, $label = NULL, $namespace = NULL, $defaultValue = NULL) {
            return $form[$name] = new WebChemistry\Images\Addons\UploadControl($label, $namespace, $defaultValue);
        });
    }
    
    /**
     * @return array
     */
    protected function uploadArray($value) {
        return $value instanceof Nette\Http\FileUpload ? array($value) : (array) $value;
    }
    
    protected function getUploadValue(array $values) {
        if ($this->multiple && $values) {
            return $values;
        } else if ($values) {
            return $values[0];
        } else {
            return NULL;
        }
    }

}