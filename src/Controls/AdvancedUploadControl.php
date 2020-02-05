<?php declare(strict_types = 1);

namespace WebChemistry\Images\Controls;

use Nette\Application\IPresenter;
use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
use Nette\Forms;
use Nette\Utils\Html;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\StateResource;
use WebChemistry\Images\Resources\Transfer\UploadResource;

class AdvancedUploadControl extends Forms\Controls\UploadControl {

	/** @var string|null */
	private $namespace;

	/** @var IFileResource|null */
	private $defaultValue;

	/** @var IImageStorage|null */
	private $imageStorage = false;

	/** @var bool */
	private $preview = true;

	/** @var bool|string */
	private $delete = false;

	/** @var bool */
	private $toDelete = false;

	/** @var bool|string|object */
	private $required = false;

	/** @var string|null */
	private $previewAlias;

	public function __construct(?string $label = null, ?string $namespace = null) {
		parent::__construct($label, false);

		$this->namespace = $namespace;

		$this->getRules()->removeRule(Form::MAX_FILE_SIZE);
		$this->setMaxFileSize(Forms\Helpers::iniGetSize('upload_max_filesize'));
	}

	public function setMaxFileSize(int $size, string $message = null) {
		$this->addRule(function ($control, $limit) {
			/** @var UploadResource|null $value */
			$value = $control->getValue()->getUpload();

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

		if (!$this->hasErrors() && $this->delete) {
			if ($this->getHttpData(Form::DATA_TEXT, '_check') && !$this->required) {
				$this->toDelete = true;
			}
		}
	}

	public function setValue($value) {
		if ($value === null || $value instanceof IFileResource) {
			$this->defaultValue = $value;
		}

		return $this;
	}

	/**
	 * @param string|null $previewAlias
	 * @return static
	 */
	public function setPreviewAlias(?string $previewAlias) {
		$this->previewAlias = $previewAlias;

		return $this;
	}

	public function setRequired($value = true) {
		$this->required = $value;

		return $this;
	}

	/**
	 * @param bool $preview
	 * @return static
	 */
	public function setPreview(bool $preview = true) {
		$this->preview = $preview;

		return $this;
	}

	/**
	 * @param bool|string $delete
	 * @return static
	 */
	public function setDelete($delete) {
		$this->delete = $delete;

		return $this;
	}

	protected function isValueOk(): bool {
		return $this->value && $this->value->isOk() && $this->value->isImage();
	}

	public function getValue(): StateResource {
		$upload = null;
		if ($this->isValueOk()) {
			$upload = new UploadResource($this->value);
			$upload->setNamespace($this->namespace);
		}

		return new StateResource($this->defaultValue, $upload, $this->toDelete);
	}

	private function getImageStorage(): ?IImageStorage {
		if ($this->imageStorage === false) {
			/** @var \Nette\Application\UI\Presenter|null $presenter */
			$presenter = $this->lookup(IPresenter::class, false);
			if ($presenter) {
				/** @var IImageStorage $storage */
				$storage = $presenter->getContext()->getByType(IImageStorage::class, false);
				$this->imageStorage = $storage;
			} else {
				$this->imageStorage = null;
			}
		}

		return $this->imageStorage;
	}

	public function getPreviewPart(?string $placeholder = null): ?Html {
		if (($this->delete || $this->preview || $placeholder) && $this->getImageStorage()) {
			$preview = Html::el('div', [
				'class' => 'wch-upload-preview-container',
			]);
			if ($this->preview && $this->defaultValue) {
				if ($this->previewAlias) {
					$this->defaultValue->setAlias($this->previewAlias);
				}
				$link = $this->getImageStorage()->link($this->defaultValue);
				$preview->create('img', [
					'src' => $link,
					'class' => 'wch-upload-preview',
					'data-placeholder' => $placeholder,
				]);
			} else if ($placeholder) {
				$preview->create('img', [
					'src' => $placeholder,
					'class' => 'wch-upload-preview',
					'data-placeholder' => $placeholder,
				]);
			}

			return $preview;
		}

		return null;
	}

	public function getCheckboxPart(): ?Html {
		if ($this->delete && !$this->required && $this->defaultValue) {
			$wrapper = Html::el('');
			$label = $wrapper->create('label');
			$label->create('input', [
				'type' => 'checkbox',
				'id' => $this->getHtmlId() . '_check',
				'name' => $this->getName() . '_check',
			]);
			$label->create('')->setText((string) $this->delete);

			return $wrapper;
		}

		return null;
	}

	public function getControlPart(): ?Html {
		$control = parent::getControl();

		return $control instanceof Html ? $control : Html::el()->setHtml($control);
	}

	public function getControl() {
		if ($this->required && !$this->defaultValue) {
			parent::setRequired($this->required);
		}

		$container = Html::el('div', [
			'class' => 'wch-upload-container',
		]);

		if ($preview = $this->getPreviewPart()) {
			$container->insert(null, $preview);
		}
		if ($checkbox = $this->getCheckboxPart()) {
			$container->insert(null, $checkbox);
		}
		$container->insert(null, $this->getControlPart());

		return $container;
	}

	/**
	 * @param string|null $namespace
	 */
	public function setNamespace(?string $namespace) {
		$this->namespace = $namespace;
	}

	public static function register(string $controlName = 'addImagePreviewUpload') {
		Forms\Container::extensionMethod(Container::class . '::' . $controlName, static::class . '::addInput');
	}

	public static function addInput(Forms\Form $form, string $name, ?string $label = null, ?string $namespace = null) {
		return $form[$name] = new static($label, $namespace);
	}

}
