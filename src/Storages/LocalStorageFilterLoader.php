<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages;

use Nette\Utils\Image;
use WebChemistry\Images\Filters\BaseFilterLoader;
use WebChemistry\Images\Filters\FilterArgs;
use WebChemistry\Images\Exceptions\ImageStorageException;

final class LocalStorageFilterLoader extends BaseFilterLoader {

	/** @var int[] */
	private static $flags = [
		'shrink' => 0b0001,
		'shrink_only' => 0b0001,
		'stretch' => 0b0010,
		'fit' => 0b0000,
		'fill' => 0b0100,
		'exact' => 0b1000,
	];

	public function resize(FilterArgs $args, $width, $height, $flags = Image::FIT) {
		$image = $args->getImage();

		// TODO: string|int
		if ($flags) {
			$flags = $this->converseFlags(array_slice(func_get_args(), 3));
		}
		$image->resize($width, $height, $flags);
	}


	/**
	 * @param mixed[] $flags
	 * @throws ImageStorageException
	 */
	protected function converseFlags(array $flags): int {
		$result = 0b0000;
		foreach ($flags as $flag) {
			if (!isset(self::$flags[$flag])) {
				throw new ImageStorageException(sprintf('Flag %s not exists', $flag));
			}
			$result |= self::$flags[$flag];
		}
		return $result;
	}

}
