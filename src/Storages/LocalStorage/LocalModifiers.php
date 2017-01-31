<?php

namespace WebChemistry\Images\Storages\LocalStorage;


use Nette\Utils\Image;
use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\ILoader;
use WebChemistry\Images\Modifiers\ModifierContainer;

class LocalModifiers implements ILoader {

	/** @var array */
	private static $flags = [
		'shrink' => 0b0001,
		'shrink_only' => 0b0001,
		'stretch' => 0b0010,
		'fit' => 0b0000,
		'fill' => 0b0100,
		'exact' => 0b1000,
	];

	public function load(ModifierContainer $modifierContainer) {
		$modifierContainer->addModifier('crop', function (Image $image, $left, $top, $width, $height) {
			$image->crop($left, $top, $width, $height);
		});
		$modifierContainer->addModifier('resize', [$this, 'resize']);

		/////////////////////////////////////////////////////////////////

		$modifierContainer->addModifier('quality', NULL);
		$modifierContainer->addModifier('sharpen', function (Image $image) {
			$image->sharpen();
		});
		$modifierContainer->addModifier('baseUri', NULL);
		$modifierContainer->addModifier('defaultImage', NULL);
	}

	public function resize(Image $image, $width, $height, $flag = Image::FIT) {
		if ($flag) {
			$flag = $this->converseFlags(array_slice(func_get_args(), 3));
		}
		$image->resize($width, $height, $flag);
	}

	public function quality(IImage $image, $quality) {
		$image->setQuality($quality);
	}

	protected function converseFlags(array $flags) {
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
