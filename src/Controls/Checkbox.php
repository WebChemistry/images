<?php

namespace WebChemistry\Images\Controls;

use Nette\Utils\Html;
use WebChemistry\Images\AbstractStorage;
use WebChemistry\Images\Image\PropertyAccess;

class Checkbox extends \Nette\Forms\Controls\Checkbox {

	const CHECKBOX_NAME = '_checkbox';

	/** @var string */
	private $imageName;

	/** @var AbstractStorage */
	private $storage;

	/** @var string */
	private $prepend;

	/** @var int */
	private $width;

	/** @var int */
	private $height;

	/** @var bool */
	private $isOk;

	/**
	 * @var string
	 * @deprecated
	 */
	public static $labelContent = NULL;

	/** @var string */
	public static $globalCaption = 'Delete this image?';

	public function __construct($label = NULL) {
		if (self::$labelContent !== NULL) {
			trigger_error("labelContent is deprected, use globalCaption instead.", E_USER_DEPRECATED);
			self::$globalCaption = self::$labelContent;
		}
		parent::__construct($label ? : self::$globalCaption);
	}

	/**
	 * @return bool
	 */
	public function isOk() {
		if ($this->isOk === NULL) {
			$this->isOk = $this->imageName && $this->getImageClass()->isExists();
		}

		return $this->isOk;
	}

	/************************* Getters **************************/

	/**
	 * @return PropertyAccess
	 */
	public function getImageClass() {
		$image = $this->storage->createImage();
		$image->setAbsoluteName($this->imageName);

		return $image;
	}

	/**
	 * @return string
	 */
	public function getControl($onlyPreview = FALSE) {
		if (!$this->isOk()) {
			return NULL;
		}

		$html = Html::el('div');
		$html->class[] = 'preview-image-container';
		$html->setHtml(Html::el('img')->src($this->getImageClass()->getLink())->height($this->height)
			->width($this->width));

		return $html . Html::el('div')->setHtml($onlyPreview ? NULL : parent::getControl());
	}

	/**
	 * @return string
	 */
	public function getHtmlName() {
		return $this->prepend . self::CHECKBOX_NAME;
	}

	/**
	 * @return null
	 */
	public function getHtmlId() {
		return NULL;
	}

	/************************* Setters **************************/

	/**
	 * @param string $prepend
	 * @return Checkbox
	 */
	public function setPrepend($prepend) {
		$this->prepend = $prepend;

		return $this;
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
	 * @param string $imageName
	 * @return Checkbox
	 */
	public function setImageName($imageName) {
		$this->isOk = NULL;
		$this->imageName = $imageName;

		return $this;
	}

	/**
	 * @param int $height
	 * @return Checkbox
	 */
	public function setHeight($height) {
		$this->height = $height;

		return $this;
	}

	/**
	 * @param int $width
	 * @return Checkbox
	 */
	public function setWidth($width) {
		$this->width = $width;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getImageName() {
		return $this->imageName;
	}

}
