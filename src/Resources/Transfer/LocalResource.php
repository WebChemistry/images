<?php

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;

class LocalResource extends TransferResource {

	/** @var string */
	private $file;

	/**
	 * @param string $file
	 * @param string $id
	 */
	public function __construct($file, $id) {
		$this->file = $file;
		$this->setId($id);
	}

	/**
	 * @return Image
	 */
	public function toImage(IImageFactory $factory = NULL) {
		if ($factory) {
			return $factory->createFromFile($this->file);
		}

		return Image::fromFile($this->file);
	}

	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->file;
	}

}
