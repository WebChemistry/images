<?php

namespace WebChemistry\Images\Controls;


use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
use Nette\Forms;
use Nette\Object;
use WebChemistry\Images\Resources\Transfer\UploadResource;

class UploadControl extends Forms\Controls\UploadControl {

	/** @var string */
	private $namespace;

	public function __construct($label = NULL, $namespace = NULL) {
		parent::__construct($label, FALSE);

		$this->namespace = $namespace;
	}

	public function loadHttpData() {
		parent::loadHttpData();

		if ($this->value->isOk() && !$this->value->isImage()) {
			$this->addError(Forms\Validator::$messages[Form::IMAGE]);
		}
	}

	/**
	 * @return UploadResource|null
	 */
	public function getValue() {
		if (!$this->value->isOk()) {
			return null;
		}
		$value = new UploadResource($this->value);
		$value->setNamespace($this->namespace);

		return $value;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	public static function register($controlName = 'addImageUpload') {
		Object::extensionMethod(Container::class . '::' . $controlName, function ($form, $name, $label = NULL, $namespace = NULL) {
			return $form[$name] = new self($label, $namespace);
		});
	}

}
