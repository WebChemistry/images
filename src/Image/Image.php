<?php

namespace WebChemistry\Images\Image;


class Image extends \Nette\Utils\Image implements IImage {

	/** @var int */
	private $quality = 80;

	/**
	 * @param int $quality
	 * @return static
	 */
	public function setQuality($quality = 80) {
		$this->quality = $quality;

		return $this;
	}

	/**
	 * Saves image to the file.
	 * @param  string  filename
	 * @param  int  quality (0..100 for JPEG and WEBP, 0..9 for PNG)
	 * @param  int  optional image type
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function save($file = NULL, $quality = NULL, $type = NULL) {
		return parent::save($file, $quality === NULL ? $this->quality : $quality, $type);
	}


}