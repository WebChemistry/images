<?php declare(strict_types = 1);

namespace WebChemistry\Images\Controls;

use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
use Nette\Forms;
use WebChemistry\Images\Resources\ResourceException;
use WebChemistry\Images\Resources\Transfer\UploadResource;

class UploadControl extends Forms\Controls\UploadControl {

	/** @var string|null */
	protected $namespace;

	/** @var int|null */
	protected $maxSize;

	/** @var string|null */
	protected $maxSizeMessage;

	public function __construct(?string $label = null, ?string $namespace = null) {
		parent::__construct($label, false);

		$this->namespace = $namespace;

		$this->getRules()->removeRule(Form::MAX_FILE_SIZE);

		$this->maxSize = Forms\Helpers::iniGetSize('upload_max_filesize');
	}

	public function validate(): void {
		$this->addRule(function (Forms\IControl $control): bool {
			return $this->validateMaxSize($control, $this->maxSize);
		}, $this->maxSizeMessage ?? Forms\Validator::$messages[Forms\Form::MAX_FILE_SIZE]);

		parent::validate();
	}

	protected function validateMaxSize(Forms\IControl $control, int $size): bool {
		/** @var UploadResource|null $value */
		$value = $control->getValue();

		if ($value === null) {
			return true;
		}

		$file = $value->getUpload();
		if (!$file->isOk() || $file->getSize() > $size || $file->getError() === UPLOAD_ERR_INI_SIZE) {
			return false;
		}

		return true;
	}

	public function setMaxFileSize(int $size, string $message = null) {
		$this->maxSize = $size;
		$this->maxSizeMessage = $message;

		return $this;
	}

	public function loadHttpData(): void {
		parent::loadHttpData();

		if ($this->value->isOk() && !$this->value->isImage()) {
			$this->addError(Forms\Validator::$messages[Form::IMAGE]);
		}
	}

	/**
	 * @throws ResourceException
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

	public function setNamespace(?string $namespace) {
		$this->namespace = $namespace;

		return $this;
	}

	public static function register(string $controlName = 'addImageUpload') {
		Forms\Container::extensionMethod(Container::class . '::' . $controlName, static::class . '::addInput');
	}

	public static function addInput(Forms\Container $form, string $name, ?string $label = null, ?string $namespace = null) {
		return $form[$name] = new static($label, $namespace);
	}

}
