<?php

namespace WebChemistry\Images\Controls;

use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
use Nette\Forms;
use WebChemistry\Images\Resources\Transfer\UploadResource;

class UploadControl extends Forms\Controls\UploadControl {

	/** @var string|null */
	private $namespace;

	public function __construct(?string $label = null, ?string $namespace = null) {
		parent::__construct($label, false);

		$this->namespace = $namespace;
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
		if (!$this->value->isOk()) {
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

	public static function register($controlName = 'addImageUpload') {
		Forms\Container::extensionMethod(Container::class . '::' . $controlName, self::class . '::addInput');
	}

	public static function addInput(Forms\Form $form, string $name, ?string $label = null, ?string $namespace = null) {
		return $form[$name] = new self($label, $namespace);
	}

}
