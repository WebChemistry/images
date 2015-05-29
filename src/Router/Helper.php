<?php

namespace WebChemistry\Images\Router;

class Helper {

	const DELIMETER = ':';

	/**
	 * @param string $name
	 * @return string
	 */
	public static function encodeName($name) {
		if (!$name) {
			return NULL;
		}

		$name = str_replace('/', self::DELIMETER, $name);

		$pos = strrpos($name, '.');

		if ($pos !== FALSE) {
			$name = substr_replace($name, self::DELIMETER, $pos, 1);
		}

		return $name;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function decodeName($name) {
		if (!$name) {
			return NULL;
		}

		$name = str_replace(self::DELIMETER, '/', $name);

		$pos = strrpos($name, '/');

		if ($pos !== FALSE) {
			$name = substr_replace($name, '.', $pos, 1);
		}

		return $name;
	}
}
