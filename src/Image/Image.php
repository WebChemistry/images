<?php declare(strict_types = 1);

namespace WebChemistry\Images\Image;

use Nette\InvalidArgumentException;
use WebChemistry\Images\Resources\IResource;

class Image extends \Nette\Utils\Image implements IImage {

	/** @var int|null */
	private $quality;

	/**
	 * @param int|null $quality
	 * @return static
	 */
	public function setQuality(?int $quality) {
		$this->quality = $quality;

		return $this;
	}

	/**
	 * Saves image to the file.
	 * @param string $file  filename
	 * @param int|null $quality  quality (0..100 for JPEG and WEBP, 0..9 for PNG)
	 * @param int $type  optional image type
	 */
	public function save(string $file, int $quality = null, int $type = null): void {
		parent::save($file, $quality === null ? $this->quality : $quality, $type);
	}

	/**
	 * @param IResource|string $resource
	 *
	 * @return int
	 * @throws \Nette\InvalidArgumentException
	 */
	public static function getImageType($resource): int {
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
