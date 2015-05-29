<?php

namespace WebChemistry\Images\Helpers;

use WebChemistry\Images\Bridges\Nette\Image;

class Crop implements IHelper {

	/**
	 * @param array $array
	 * @return array
	 */
	private function formatParameters(array $array) {
		return array_map(function ($value) {
			return trim($value);
		}, $array);
	}

	/**
	 * @param Image  $image
	 * @param string $parameter
	 */
	public function invoke(Image &$image, $parameter) {
		call_user_func_array([$image, 'crop'], $this->formatParameters(explode(',', $parameter)));
	}
}