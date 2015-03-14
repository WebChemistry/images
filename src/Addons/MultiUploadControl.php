<?php

namespace WebChemistry\Images\Addons;

use Nette, WebChemistry;

class MultiUploadControl extends UploadControl {
    
    const DELETE_NAME = 'delete';
    const UPLOAD_NAME = 'upload';
    const ADD_NAME = '_add';
    
    const MAX_IMAGES = 'WebChemistry\Images\Addons\Replicator::validateMaxImages';
    
    public $hasActions = TRUE;
    
    public function __construct($label = NULL, $namespace = NULL, $defaultValue = NULL) {
        parent::__construct($label, $namespace, $defaultValue);
        
        $this->multiple = TRUE;
        $this->control->multiple = TRUE;
    }
    
    protected function getCheckboxName($getter = FALSE) {
        if ($getter === TRUE) {
            return Nette\Forms\Helpers::generateHtmlName($this->parent->lookupPath('Nette\Forms\Form')) . '[' . $this->checkboxName . ']';
        }
        
        return $this->checkboxName;
    }
    
    protected function attached($form) {
        if ($form instanceof Nette\Application\IPresenter) {
            if (!$this->hasActions && !$this->getStorage()->create($this->default)->exists()) {
                unset($this->parent[$this->getName()]);
            }
        }
        
        parent::attached($form);
    }
    
    public function getControl() {
        $control = parent::getControl();
        
        if ($this->useCheckbox()) {
            $this->setOption('class', 'imu-container imu-delete-container');
        }
        
        $container = $this->lookup('Nette\Forms\Container');
        $replicator = $this->lookup('Kdyby\Replicator\Container');
        
        $componentCount = count($replicator->getComponents());
        
        if (count($container->getComponents()) === 1) {
            return $control;
        }
        
        $control .= (string) $container[self::DELETE_NAME]->getControl();
        
        if ($container->getName() == $componentCount - 2) {
            $addButton = $replicator[$replicator->getName() . self::ADD_NAME];
            $control .= $addButton->getControl();
        }
        
        return $control;
    }
    
    public static function register($controlName = 'addImageMultiUpload', $addLabel = 'Add', $deleteLabel = 'Delete') {
        if (!is_string($controlName)) {
            throw new WebChemistry\Images\ImageStorageException(sprintf('Control name must be string, %s given', gettype($controlName)));
        }
        
        Nette\Object::extensionMethod('Nette\Forms\Container::' . $controlName, function ($form, $name, $label = NULL, $namespace = NULL, $defaultValue = NULL, $count = 1, $maxSize = NULL) use ($addLabel, $deleteLabel) {
            if ($count < 1) {
                throw new WebChemistry\Images\ImageStorageException('MultipleUpload must have min. 1 upload control.');
            }
            
            $dynamic = $form[$name] = new Replicator(function ($container) use ($name, $label, $namespace, $defaultValue, $deleteLabel) {
                $container[WebChemistry\Images\Addons\MultiUploadControl::UPLOAD_NAME] = $control = new WebChemistry\Images\Addons\MultiUploadControl($label, $namespace, $defaultValue);
                
                $control->setOption('class', 'imu-container')
                            ->setAttribute('class', 'imu-control');
                
                $container->addSubmit(WebChemistry\Images\Addons\MultiUploadControl::DELETE_NAME, $deleteLabel)
                        ->setAttribute('onclick', '$.multiUpload.removeInput(this); return false;')
                        ->setAttribute('class', 'imu-delete')
                        ->addRemoveOnClick();
            }, $count, FALSE);
            
            $dynamic->currentGroup = $form->currentGroup;

            $dynamic->addSubmit($name . WebChemistry\Images\Addons\MultiUploadControl::ADD_NAME, $addLabel)
                    ->setAttribute('class', 'imu-add')
                    ->setAttribute('onclick', '$.multiUpload.addInput(this); return false;')
                    ->addCreateOnClick(TRUE);
            
            return $dynamic;
        });
        
    }
}
