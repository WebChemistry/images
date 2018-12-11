<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Resources\IResource;

class BaseModifiers implements ILoader {

	/** @var array */
	private static $flags = [
		'shrink' => 0b0001,
		'shrink_only' => 0b0001,
		'stretch' => 0b0010,
		'fit' => 0b0000,
		'fill' => 0b0100,
		'exact' => 0b1000,
	];

	/**
	 * {@inheritdoc}
	 */
	public function load(ModifierContainer $modifierContainer): void {
		$modifierContainer->addModifier('crop', function (Image $image, $left, $top, $width, $height) {
			$image->crop($left, $top, $width, $height);
		});
		$modifierContainer->addModifier('resize', [$this, 'resize']);
		$modifierContainer->addModifier('quality', [$this, 'quality']);
		$modifierContainer->addModifier('sharpen', function (Image $image) {
			$image->sharpen();
		});

		/////////////////////////////////////////////////////////////////

		$modifierContainer->addResourceModifier('defaultImage', function (IResource $resource, $image) {
			$resource->setDefaultImage($image);
		});

		$modifierContainer->addResourceModifier('baseUrl', function (IResource $resource, $baseUrl = true) {
			$resource->setBaseUrl($baseUrl);
		});

		// deprecated
		$modifierContainer->addResourceModifier('baseUri', function (IResource $resource, $baseUrl = true) {
			$resource->setBaseUrl($baseUrl);
		});
	}

	/**
	 * @param \Nette\Utils\Image $image
	 * @param int $width
	 * @param int $height
	 * @param int $flag
	 * @throws ImageStorageException
	 */
	public function resize(Image $image, $width, $height, $flag = Image::FIT): void {
		if ($flag) {
			$flag = $this->converseFlags(array_slice(func_get_args(), 3));
		}
		$image->resize($width, $height, $flag);
	}

	/**
	 * @param IImage $image
	 * @param int $quality
	 */
	public function quality(IImage $image, $quality): void {
		$image->setQuality($quality);
	}

	/**
	 * @param array $flags
	 * @return int
	 * @throws ImageStorageException
	 */
	protected function converseFlags(array $flags): int {
		$result = 0b0000;
		foreach ($flags as $flag) {
			if (!isset(self::$flags[$flag])) {
				throw new ImageStorageException("Flag '$flag' not exists.");
			}

			$result |= self::$flags[$flag];
		}

		return $result;
	}

}
