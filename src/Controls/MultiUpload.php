<?php

namespace WebChemistry\Images\Controls;

use Nette\Application\IPresenter;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Http\FileUpload;
use Nette\Object;
use WebChemistry\Images\AbstractStorage;
use WebChemistry\Images\ImageStorageException;

class MultiUpload extends UploadControl {

	/** @var string */
	private $namespace;

	/** @var array */
	private $defaultValues = [];

	/** @var Checkbox[] */
	private $checkboxes = [];

	/** @var AbstractStorage */
	private $storage;

	/** @var array */
	private $toDelete = [];

	/** @var array */
	private $toUpload = [];

	/** @var bool */
	private $isValidated = FALSE;

	/** @var bool */
	private $required = FALSE;

	/** @var Checkbox[] */
	private $checkboxesFine = [];

	/** @var array images to delete, not exists */
	private $wrongImages = [];

	/** @var array Used in success,error callbacks */
	private $uploadedImages = [];

	/**
	 * @param string $label
	 */
	public function __construct($label = NULL) {
		parent::__construct($label, TRUE);

		$this->addCondition(Form::FILLED)
			->addRule(Form::IMAGE)
			->endCondition();

		$this->monitor('Nette\Application\IPresenter');
	}

	protected function attached($form) {
		parent::attached($form);

		if ($form instanceof IPresenter) {
			if (isset($form->imageStorage) && $form->imageStorage instanceof AbstractStorage) {
				$this->storage = $form->imageStorage;
			} else {
				$this->storage = $form->context->getByType('WebChemistry\Images\AbstractStorage');
			}

			$form = $this->getForm();
			$htmlName = str_replace('[]', '', $this->getHtmlName());
			foreach ($this->defaultValues as $i => $defaultValue) {
				$checkbox = new Checkbox();
				$checkbox->setParent($form, $i);
				$checkbox->setPrepend($htmlName . $i);
				$checkbox->setStorage($this->storage);
				$checkbox->setImageName($defaultValue);

				$this->checkboxes[] = $checkbox;
			}

			if ($this->required && !$this->getCheckboxesFine()) {
				$this->addRule(Form::FILLED, $this->getRequiredMessage());
			}
		}
	}

	/**
	 * @return string
	 */
	private function getRequiredMessage() {
		return is_string($this->required) ? $this->required : Validator::$messages[Form::FILLED];
	}

	public function loadHttpData() {
		parent::loadHttpData();

		$this->validate();
		$this->isValidated = TRUE; // Disable validation

		foreach ($this->getCheckboxesFine() as $checkbox) {
			$checkbox->loadHttpData();
			if ($checkbox->getValue()) {
				$this->toDelete[] = $checkbox->getImageName();
			}
		}

		foreach ($this->getValueArray() as $upload) {
			if ($upload instanceof FileUpload && $upload->isOk()) {
				$this->toUpload[] = $upload;
			}
		}

		if ($this->required && !$this->toUpload && $this->getCheckboxesFine() &&
			count($this->toDelete) === count($this->getCheckboxesFine())) {
			$this->addError($this->getRequiredMessage());
		}

		$form = $this->getForm();
		if ($form->isValid()) {
			$form->onSubmit[] = [$this, 'errorCallback'];
			$this->successCallback();
		}
	}

	public function errorCallback(Form $form) {
		if (!$form->isValid() && $this->uploadedImages) {
			foreach ($this->uploadedImages as $image) {
				$this->storage->delete($image);
			}
		}
	}

	public function successCallback() {
		$values = $this->defaultValues;
		foreach ($this->toDelete as $index => $value) {
			$this->storage->delete($value);
			unset($values[array_search($value, $this->defaultValues)]);
		}

		foreach ($this->wrongImages as $value) {
			if (($index = array_search($value, $values)) !== FALSE) {
				unset($values[$index]);
			}
		}

		foreach ($this->toUpload as $upload) {
			$this->uploadedImages[] = $values[] = $this->storage->saveUpload($upload, $this->namespace);
		}

		$this->value = array_values($values); // Reset keys
	}

	/************************* Setters **************************/

	/**
	 * @param bool $value
	 * @return self
	 */
	public function setRequired($value = TRUE) {
		$this->required = $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRequired() {
		return FALSE;
	}

	/**
	 * @param AbstractStorage $storage
	 * @return Upload
	 */
	public function setStorage(AbstractStorage $storage) {
		$this->storage = $storage;

		return $this;
	}

	/**
	 * @param string|array $value
	 * @return self
	 * @throws ImageStorageException
	 */
	public function setValue($value) {
		$this->defaultValues = (array) $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string $namespace
	 * @return Upload
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;

		return $this;
	}

	/************************* Getters **************************/

	/**
	 * @return Checkbox[]
	 */
	public function getCheckboxes() {
		return $this->checkboxes;
	}

	/**
	 * @return Checkbox[]
	 */
	public function getCheckboxesFine() {
		if (!$this->checkboxesFine || !$this->checkboxes) {
			foreach ($this->checkboxes as $index => $checkbox) {
				if ($checkbox->isOk()) {
					$this->checkboxesFine[$index] = $checkbox;
				} else {
					$this->wrongImages[] = $checkbox->getImageName();
				}
			}
		}

		return $this->checkboxesFine;
	}

	/**
	 * @return string
	 */
	protected function renderCheckboxes() {
		$return = '';
		foreach ($this->getCheckboxesFine() as $checkbox) {
			$return .= $checkbox->getControl();
		}

		return $return;
	}

	/**
	 * @return string
	 */
	public function getControl() {
		$control = parent::getControl();
		$checkboxes = $this->renderCheckboxes();
		if ($this->required && !$checkboxes) {
			$control->addAttributes(['required' => TRUE]);
		}

		return $control . $checkboxes;
	}

	public function validate() {
		if (!$this->isValidated) {
			parent::validate();
		}
	}

	/**
	 * @return array
	 */
	private function getValueArray() {
		return is_array($this->value) ? $this->value : [$this->value];
	}

	public static function register($controlName = 'addMultiImageUpload') {
		if (!is_string($controlName)) {
			throw new ImageStorageException(sprintf('Control name must be a string, %s given', gettype($controlName)));
		}

		Object::extensionMethod('Nette\Forms\Container::' . $controlName, function ($form, $name, $label = NULL, $namespace = NULL) {
			$control = new self($label);
			$control->setNamespace($namespace);

			return $form[$name] = $control;
		});
	}

}
