<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\Params\ModifierParam;
use WebChemistry\Images\Modifiers\Params\ResourceModifierParam;
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
		$modifierContainer->addModifier('crop', function (ModifierParam $param, $left, $top, $width, $height) {
			$image = $param->getImage();

			$image->crop($left, $top, $width, $height);
		});
		$modifierContainer->addModifier('resize', [$this, 'resize']);
		$modifierContainer->addModifier('quality', [$this, 'quality']);
		$modifierContainer->addModifier('sharpen', function (ModifierParam $param) {
			$image = $param->getImage();

			$image->sharpen();
		});

		/////////////////////////////////////////////////////////////////

		$modifierContainer->addResourceModifier('defaultImage', function (ResourceModifierParam $param, $image) {
			$resource = $param->getResource();

			$resource->setDefaultImage($image);
		});

		$modifierContainer->addResourceModifier('baseUrl', function (ResourceModifierParam $param, $baseUrl = true) {
			$resource = $param->getResource();

			$resource->setBaseUrl($baseUrl);
		});

		// deprecated
		$modifierContainer->addResourceModifier('baseUri', function (ResourceModifierParam $param, $baseUrl = true) {
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
