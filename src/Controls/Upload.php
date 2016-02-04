<?php

namespace WebChemistry\Images\Controls;

use Nette\Application\IPresenter;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Http\FileUpload;
use Nette\Object;
use WebChemistry\Images\AbstractStorage;
use WebChemistry\Images\Image\PropertyAccess;
use WebChemistry\Images\ImageStorageException;

class Upload extends UploadControl {

	/** @var string */
	private $namespace;

	/** @var string */
	private $defaultValue;

	/** @var Checkbox */
	private $checkbox;

	/** @var AbstractStorage */
	private $storage;

	/** @var bool */
	private $delete = FALSE;

	/** @var bool */
	private $isValidated = FALSE;

	/** @var bool */
	private $required = FALSE;

	/**
	 * @param string $label
	 */
	public function __construct($label = NULL) {
		parent::__construct($label, FALSE);

		$this->addCondition(Form::FILLED)
			->addRule(Form::IMAGE)
			->endCondition();

		$this->monitor('Nette\Application\IPresenter');
		$this->checkbox = new Checkbox;
	}

	protected function attached($form) {
		parent::attached($form);

		if ($form instanceof Form) {
			$this->checkbox->setParent($form, $form->getName());
			if (!$form->onSuccess) {
				$form->onSuccess = [];
			}
			array_unshift($form->onSuccess, [$this, 'successCallback']);
		}

		if ($form instanceof IPresenter) {
			if (isset($form->imageStorage) && $form->imageStorage instanceof AbstractStorage) {
				$this->storage = $form->imageStorage;
			} else {
				$this->storage = $form->context->getByType('WebChemistry\Images\AbstractStorage');
			}

			$this->checkbox->setPrepend($this->getHtmlName());
			$this->checkbox->setStorage($this->storage);
			$this->checkbox->setImageName($this->defaultValue);

			if ($this->required && !$this->checkbox->isOk()) {
				$this->addRule(Form::FILLED, is_string($this->required) ? $this->required : NULL);
			}
		}
	}

	public function loadHttpData() {
		parent::loadHttpData();

		$this->validate();
		$this->isValidated = TRUE; // Disable validation

		if ($this->required && $this->checkbox->isOk()) {
			if ($this->value->isOk()) {
				$this->delete = TRUE;
			} else {
				$this->value = $this->defaultValue;
			}
		} else if ($this->checkbox->isOk()) { // Checkbox process
			$this->checkbox->loadHttpData();
			$this->delete = $this->checkbox->getValue();
			if ($this->delete) {
				$this->value = NULL;
			} else {
				$this->value = $this->defaultValue;
			}
		} else if (!$this->value->isOk()) {
			$this->value = NULL;
		}
	}

	public function successCallback() {
		if ($this->delete) {
			$this->storage->delete($this->defaultValue);
		}

		if ($this->value instanceof FileUpload && $this->value->isOk()) { // Upload
			$this->value = $this->storage->saveUpload($this->value, $this->namespace);
			$this->checkbox->setImageName($this->value); // Show image after send
		}
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
	 * @param string $value
	 * @return self
	 * @throws ImageStorageException
	 */
	public function setValue($value) {
		if ($value !== NULL && !is_string($value)) {
			throw new ImageStorageException(sprintf('Value must be string, %s given.', gettype($value)));
		}
		$this->defaultValue = $value;

		return $this;
	}

	/**
	 * FALSE - Form is not successful or control is not valid.
	 * NULL - Image was deleted or was not uploaded
	 * STRING - Image was uploaded or contains default image
	 *
	 * @return bool|null|string
	 */
	public function getValue() {
		if ($this->isValidated && $this->value instanceof FileUpload) {
			return FALSE;
		}

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
	 * @return Checkbox
	 */
	public function getCheckbox() {
		return $this->checkbox;
	}

	/**
	 * @return string
	 */
	public function getControl() {
		$control = parent::getControl();
		if ($this->required && !$this->checkbox->isOk()) {
			$control->addAttributes(['required' => TRUE]);
		}
		if ($this->checkbox->isOk()) {
			return $this->checkbox->getControl($this->required) . ($this->required ? $control : NULL);
		}

		return $control;
	}

	/************************* UploadControl **************************/

	public function validate() {
		if (!$this->isValidated) {
			parent::validate();
		}
	}

	public static function register($controlName = 'addImageUpload') {
		if (!is_string($controlName)) {
			throw new ImageStorageException(sprintf('Control name must be a string, %s given', gettype($controlName)));
		}

		Object::extensionMethod('Nette\Forms\Container::' . $controlName, function ($form, $name, $label = NULL, $namespace = NULL, $defaultValue = NULL) {
			$control = new self($label);
			$control->setNamespace($namespace);
			$control->setDefaultValue($defaultValue);

			return $form[$name] = $control;
		});
	}

}
