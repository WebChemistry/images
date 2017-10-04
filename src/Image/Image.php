<?php

namespace WebChemistry\Images\Image;


use Nette\InvalidArgumentException;
use WebChemistry\Images\Resources\IResource;

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

	/**
	 * @param \WebChemistry\Images\Resources\IResource|string $resource
	 *
	 * @return int
	 * @throws \Nette\InvalidArgumentException
	 */
	public static function getImageType($resource)
	{
		$resource = $resource instanceof IResource ? $resource->getName() : $resource;
		$extensions = [
			'jpeg'  => Image::JPEG,
			'jpg'   => Image::JPEG,
			'png'   => Image::PNG,
			'gif'   => Image::GIF,
			'webp'  => Image::WEBP,
		];
		if (!isset($extensions[$extension = strtolower(pathinfo($resource, PATHINFO_EXTENSION))])) {
			throw new InvalidArgumentException("Unsupported file extension '$extension'.");
		}

		return $extensions[$extension];
	}
}
