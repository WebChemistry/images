<?php

namespace WebChemistry\Images\Controls;

use Nette\Application\IPresenter;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\Callback;
use Nette\Utils\Image;
use WebChemistry\Images\AbstractStorage;
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

	/** @var FileUpload */
	private $originalValue;

	/** @var string Used in success, error callbacks */
	private $uploadedImage;

	/** @var callable[] */
	public $onBeforeSave = [];

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

	/**
	 * @return FileUpload
	 */
	public function getOriginalValue() {
		return $this->originalValue;
	}

	protected function attached($form) {
		parent::attached($form);

		if ($form instanceof Form) {
			$this->checkbox->setParent($form, $form->getName());
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
		$this->originalValue = $this->value;

		if ($this->checkbox->isOk()) {
			$this->checkbox->loadHttpData();
			$this->delete = $this->checkbox->getValue();
		}
		if ($this->value->isOk()) {
			$this->delete = TRUE;
		}

		$form = $this->getForm();
		if ($form->isValid()) {
			$form->onSubmit[] = [$this, 'errorCallback'];
			$this->successCallback();
		}
	}

	public function errorCallback(Form $form) {
		if (!$form->isValid() && $this->uploadedImage) {
			$this->storage->delete($this->uploadedImage);
		}
	}

	protected function successCallback() {
		if ($this->delete && $this->defaultValue) {
			$this->storage->delete($this->defaultValue);
			$this->defaultValue = NULL;
		}

		if ($this->value instanceof FileUpload && $this->value->isOk()) { // Upload
			$image = $this->value->toImage();
			foreach ($this->onBeforeSave as $callback) {
				Callback::check($callback);
				$image = $callback($image);
				if (!$image instanceof Image) {
					throw new ImageStorageException('Callback must return value instance of Nette\Utils\Image');
				}
			}
			$this->uploadedImage = $this->value = $this->storage->saveImage($image, $this->value->getSanitizedName(), $this->namespace);
		} else {
			$this->value = $this->defaultValue;
		}

		$this->checkbox->setImageName($this->value);
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
	 * FileUpload - Form is not successful or control is not valid.
	 * NULL - Image was deleted or was not uploaded
	 * STRING - Image was uploaded or contains default image
	 *
	 * @return bool|null|string
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
			return $control . $this->checkbox->getControl($this->required);
		}

		return $control;
	}

	public function validate() {
		if (!$this->isValidated) {
			parent::validate();
		}
	}

	public static function register($controlName = 'addImageUpload') {
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
