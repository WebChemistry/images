<?php

namespace WebChemistry\Images\Bridges\Nette;

use Nette;

class Image extends Nette\Utils\Image {

	/** @var integer */
	private $quality;

	/**
	 * @param integer $quality
	 */
	public function setQuality($quality) {
		$this->quality = $quality;
	}

	/**
	 * Saves image to the file.
	 *
	 * @param  string $filename
	 * @param  int    $quality 0..100 (for JPEG and PNG)
	 * @param  string $type    image type
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function save($file = NULL, $quality = NULL, $type = NULL) {
		return parent::save($file, $quality ? $quality : $this->quality, $type);
	}
}