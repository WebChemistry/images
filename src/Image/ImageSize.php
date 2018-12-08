<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

use Nette\SmartObject;

/**
 * @property-read int $height
 * @property-read int $width
 */
class ImageSize {

	use SmartObject;

	/** @var int */
	protected $width;

	/** @var int */
	protected $height;

	public function __construct(int $width, int $height) {
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * @return int
	 */
	public function getHeight(): int {
		return $this->height;
	}

	/**
	 * @return int
	 */
	public function getWidth(): int {
		return $this->width;
	}

}
