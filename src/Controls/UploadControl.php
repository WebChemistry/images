<?php

namespace WebChemistry\Images\Controls;

use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
use Nette\Forms;
use WebChemistry\Images\Resources\Transfer\UploadResource;

class UploadControl extends Forms\Controls\UploadControl {

	/** @var string|null */
	protected $namespace;

	public function __construct(?string $label = null, ?string $namespace = null) {
		parent::__construct($label, false);

		$this->namespace = $namespace;
		
		$this->getRules()->removeRule(Form::MAX_FILE_SIZE);
		$this->setMaxFileSize(Forms\Helpers::iniGetSize('upload_max_filesize'));
	}

	public function setMaxFileSize(int $size, string $message = null) {
		$this->addRule(function ($control, $limit) {
			/** @var UploadResource|null $value */
			$value = $control->getValue();

			if ($value === null) {
				return true;
			}

			$file = $value->getUpload();
			if (!$file->isOk() || $file->getSize() > $limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return false;
			}

			return true;
		}, $message ?: Forms\Validator::$messages[Forms\Form::MAX_FILE_SIZE], $size);

		return $this;
	}

	public function loadHttpData(): void {
		parent::loadHttpData();

		if ($this->value->isOk() && !$this->value->isImage()) {
			$this->addError(Forms\Validator::$messages[Form::IMAGE]);
		}
	}

	/**
	 * @return null|UploadResource
	 * @throws \WebChemistry\Images\Resources\ResourceException
	 */
	public function getValue(): ?UploadResource {
		if (!$this->value) {
			return null;
		}
		if (!$this->value->isOk()) {
			return null;
		}
		if (!$this->value->isImage()) {
			return null;
		}
		$value = new UploadResource($this->value);
		$value->setNamespace($this->namespace);

		return $value;
	}

	/**
	 * @param string|null $namespace
	 */
	public function setNamespace(?string $namespace) {
		$this->namespace = $namespace;
	}

	public static function register(string $controlName = 'addImageUpload') {
		Forms\Container::extensionMethod(Container::class . '::' . $controlName, static::class . '::addInput');
	}

	public static function addInput(Forms\Form $form, string $name, ?string $label = null, ?string $namespace = null) {
		return $form[$name] = new static($label, $namespace);
	}

}
