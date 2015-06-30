<?php

namespace WebChemistry\Images\Addons;

use Nette, Nette\Utils\Html;

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

	/** @var array */
	protected $rawValue = NULL;

	/** @var bool */
	protected $isHttpData = FALSE;

	/** @var boolean */
	protected $multiple = FALSE;

	/** @var array */
	protected $previewSize = array(
		'width' => NULL,
		'height' => NULL
	);

	/** @var boolean */
	protected $checkboxValue;

	/** @var bool */
	private $isRequired = FALSE;

	/**
	 * @param string|null $label
	 * @param string|null $namespace
	 * @param boolean     $multiple
	 */
	public function __construct($label = NULL, $namespace = NULL, $defaultValue = NULL) {
		parent::__construct($label, FALSE);

		$this->namespace = $namespace;
		$this->defaultValue = $defaultValue;

		$this->addCondition(Nette\Forms\Form::FILLED)
			 ->addRule(Nette\Forms\Form::IMAGE)
			 ->endCondition();

		$this->monitor('Nette\Application\IPresenter');
	}

	public function loadHttpData() {
		if ($this->isHttpData) {
			return;
		}

		$this->isHttpData = TRUE;

		$upload = $this->getHttpData(Nette\Forms\Form::DATA_FILE);
		$checkbox = (bool) $this->getForm()->getHttpData(Nette\Application\UI\Form::DATA_LINE, $this->getCheckboxName(TRUE));
		$isRequired = $this->isRequired();

		if ($upload !== NULL && $upload->isOk()) {
			if (!$this->isImageExists() && !$checkbox) {
				$this->rawValue = $upload;
				$this->value = $this->saveUpload($upload);

				return;
			} else if (!$checkbox) {
				$this->rawValue = $this->checkboxValue = (bool) $checkbox;
				$this->value = $this->default;

				return;
			}

			$this->checkboxValue = (bool) $checkbox;
			$this->rawValue = $upload;
			$this->isDelete = TRUE;
			$this->value = $this->saveUpload($upload);
		} else if ($checkbox && !$isRequired) {
			$this->rawValue = $this->checkboxValue = (bool) $checkbox;
			$this->isDelete = TRUE;
		} /*else if ($checkbox) {
			$this->rawValue = $this->checkboxValue = (bool) $checkbox;
		}*/ else {
			$this->rawValue = $this->checkboxValue = $checkbox;
			$this->value = $this->default;
		}
	}

	protected function saveUpload($upload) {
		$return = array();
		$this->rawValue = array();

		foreach ($this->uploadArray($upload) as $file) {
			if ($file && $file->isOk()) {
				if ($file->isImage()) {
					$image = $this->getStorage()
								  ->saveUpload($file, $this->namespace);

					$return[] = $this->imageNames[] = (string) $image->getInfo();
				}

				$this->rawValue[] = $file;
			}
		}

		if (!$this->rawValue) {
			$this->rawValue = NULL;
		}

		return $this->getUploadValue($return);
	}

	/************************* Boolean getters **************************/

	public function isRequired() {
		return $this->isRequired;
	}

	protected function isImageExists() {
		return $this->default && $this->getStorage()->get($this->default)->isExists();
	}

	public function isUpload() {
		return is_array($this->rawValue);
	}

	public function isFilled() {
		if ($this->isUpload() || ($this->checkboxValue === FALSE && !$this->isRequired()) || ($this->isImageExists() && $this->checkboxValue === FALSE)) {
			return TRUE;
		}

		return FALSE;
	}

	/************************* Setters **************************/

	/**
	 * @param bool $value
	 * @return UploadControl
	 */
	public function setRequired($value = TRUE) {
		$this->getRules()->addRule(array($this, 'validateRequired'), is_string($value) ? $value : 'This field is required.');
		$this->isRequired = (bool) $value;

		return $this;
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
	 * @param null|string|int $width FALSE = is skipped
	 * @param null|string|int $height FALSE = is skipped
	 * @return $this
	 */
	public function setPreviewSize($width = FALSE, $height = FALSE) {
		if ($width !== FALSE) {
			$this->previewSize['width'] = $width;
		}

		if ($height !== FALSE) {
			$this->previewSize['height'] = $height;
		}

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

	/************************* Getters **************************/

	protected function getUploadValue(array $values) {
		if ($this->multiple && $values) {
			return $values;
		} else {
			if ($values) {
				return $values[0];
			} else {
				return NULL;
			}
		}
	}

	protected function getCheckboxName($getter = FALSE) {
		if ($getter === TRUE && !$this->parent instanceof Nette\Forms\Form) {
			return \Nette\Forms\Helpers::generateHtmlName($this->parent->lookupPath('Nette\Forms\Form') . '-' . $this->getCheckboxName());
		}

		return $this->name . '_' . $this->checkboxName;
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

	public function getRawValue() {
		return $this->rawValue;
	}

	/**
	 * Preview of image
	 *
	 * @return Html
	 */
	public function getImage() {
		$url = $this->lookup('Nette\Application\IPresenter')
					->getTemplate()->basePath . '/' . $this->getStorage()
														   ->get($this->default)
														   ->getLink();

		$image = Html::el('a')
					 ->add(Html::el('img')
							   ->setClass('upload-preview-image')
							   ->setWidth($this->previewSize['width'])
							   ->setHeight($this->previewSize['height'])
							   ->setSrc($url))
					 ->href($url);

		return Html::el('div')
				   ->setClass('upload-preview-image-container')
				   ->add($image);
	}

	/**
	 * Return checkbox or upload control.
	 *
	 * @return Html
	 */
	public function getControl() {
		if ($this->useCheckbox()) {
			$container = Html::el();

			$settings = $this->getSettings();

			$control = new Nette\Forms\Controls\Checkbox($settings['label']);

			$control->setHtmlId($this->getHtmlId());
			$control->setParent($this->lookup('Nette\Forms\Container'), $this->getCheckboxName());

			$control = $control->getControl();

			if ($this->preview) {
				$container->add($this->getImage());
			}

			$container->add($control);

			if ($this->isRequired()) {
				$container->add(parent::getControl());
			}

			return $container;
		}

		return parent::getControl();
	}

	/************************* Callbacks **************************/

	protected function attached($form) {
		if ($form instanceof Nette\Forms\Form) {
			$form->onError[] = $this->callbackCleanUp;
			$form->onSuccess[] = $this->callbackDelete;
		}

		parent::attached($form);
	}

	public function callbackDelete() {
		if ($this->isDelete) {
			$this->getStorage()
				 ->delete($this->default);
		}
	}

	public function callbackCleanUp() {
		if ($this->imageNames) {
			foreach ($this->imageNames as $name) {
				$this->getStorage()
					 ->delete($name);
			}
		}
	}

	/************************* Others **************************/

	public function useCheckbox() {
		return $this->isImageExists();
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

	/************************* Validators **************************/

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

	public function validateRequired(UploadControl $control) {
		return $control->isFilled();
	}
}