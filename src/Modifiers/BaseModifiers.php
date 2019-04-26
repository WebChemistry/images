<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use Nette\Utils\Image;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\Params\ModifierParam;
use WebChemistry\Images\Modifiers\Params\ResourceModifierParam;

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
	public function load(IModifiers $modifiers): void {
		$modifiers->addModifier('crop', function (ModifierParam $param, $left, $top, $width, $height) {
			$image = $param->getImage();

			$image->crop($left, $top, $width, $height);
		});
		$modifiers->addModifier('resize', [$this, 'resize']);
		$modifiers->addModifier('quality', [$this, 'quality']);
		$modifiers->addModifier('sharpen', function (ModifierParam $param) {
			$image = $param->getImage();

			$image->sharpen();
		});
		$modifiers->addModifier('fixOrientation', function (ModifierParam $param) {
			$image = $param->getImage();
			$path = $param->getLocation();

			if (!$path) {
				return;
			}
			$detectedFormat = @getimagesize($path)[2];
			if ($detectedFormat !== IMAGETYPE_JPEG) {
				return;
			}

			$exif = @exif_read_data($path);
			if ($exif === false) {
				return;
			}

			if (isset($exif['Orientation']) && $exif['Orientation']) {
				if (in_array($exif['Orientation'], [3, 4])) {
					$image->rotate(180, 0);

				} else if (in_array($exif['Orientation'], [5, 6])) {
					$image->rotate(-90, 0);

				} else if (in_array($exif['Orientation'], [7, 8])) {
					$image->rotate(90, 0);
				}

				if (in_array($exif['Orientation'], [2, 4, 5, 7])) {
					$image->flip(IMG_FLIP_HORIZONTAL);
				}
			}
		}, false);

		/////////////////////////////////////////////////////////////////

		$modifiers->addResourceModifier('defaultImage', function (ResourceModifierParam $param, $image) {
			$resource = $param->getResource();

			$resource->setDefaultImage($image);
		});

		$modifiers->addResourceModifier('baseUrl', function (ResourceModifierParam $param, $baseUrl = true) {
			$resource = $param->getResource();

			$resource->setBaseUrl($baseUrl);
		});

		// deprecated
		$modifiers->addResourceModifier('baseUri', function (ResourceModifierParam $param, $baseUrl = true) {
			$resource = $param->getResource();

			$resource->setBaseUrl($baseUrl);
		});
	}

	/**
	 * @param ModifierParam $param
	 * @param int $width
	 * @param int $height
	 * @param int $flag
	 * @throws ImageStorageException
	 */
	public function resize(ModifierParam $param, $width, $height, $flag = Image::FIT): void {
		$image = $param->getImage();

		if ($flag) {
			$flag = $this->converseFlags(array_slice(func_get_args(), 3));
		}
		$image->resize($width, $height, $flag);
	}

	/**
	 * @param ModifierParam $param
	 * @param int $quality
	 */
	public function quality(ModifierParam $param, $quality): void {
		$image = $param->getImage();

		$image->setQuality((int)$quality);
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
