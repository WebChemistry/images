<?php

namespace WebChemistry\Images\Image;

class ImageSize {

	/** @var int */
	protected $width;

	/** @var int */
	protected $height;

	public function __construct($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}

}
