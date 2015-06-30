<?php

namespace WebChemistry\Images\Image;

use Nette;

use WebChemistry;

class Creator extends Container {

	/** @var int */
	protected $quality;

	/** @var Info */
	protected $savedInfo;

	/**
	 * @param int $quality
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setQuality($quality) {
		if (!is_int($quality)) {
			throw new WebChemistry\Images\ImageStorageException('Quality must be integer.');
		}

		$this->quality = $quality;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getQuality() {
		return $this->quality;
	}

	/**
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function getNetteImageClass() {
		throw new WebChemistry\Images\ImageStorageException('You cannot get image class, please use callback.');
	}

	/**
	 * @param string $mimeType
	 * @return int
	 */
	protected function mimeToInteger($mimeType) {
		switch ($mimeType) {
			case 'image/jpeg':
				return Nette\Utils\Image::JPEG;
				break;
			case 'image/png':
				return Nette\Utils\Image::PNG;
				break;
			case 'image/gif':
				return Nette\Utils\Image::GIF;
		}

		return $mimeType;
	}

	/**
	 * @return Info
	 */
	public function getInfo() {
		if ($this->savedInfo) {
			return $this->savedInfo;
		}

		// Disallow create image to "resized" directory
		return $this->getOriginal();
	}

	/************************* Deprecated **************************/

	/**
	 * @deprecated
	 */
	public function createImageInfo(IImage $class = NULL) {
		trigger_error('createImageInfo is deprecated, please use getInfo');
		$this->getInfo($class);
	}
}
