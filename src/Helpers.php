<?php declare(strict_types = 1);

namespace WebChemistry\Images;

class Helpers {

	private const FOLDER_SIZE = 247;
	private const FOLDER_WITH_MD5_SIZE = 215; // 247 - 32

	const ALIAS_DEL = '.';
	const PARAM_DEL = '_';

	public static function getNameByAliases(array $aliases): string {
		uksort($aliases, function ($a, $b) {
			return strcmp($a, $b);
		});

		$name = implode(self::ALIAS_DEL, array_keys($aliases));
		if (strlen($name) > self::FOLDER_WITH_MD5_SIZE) {
			throw new \LogicException('Maximum length of aliases is ' . self::FOLDER_WITH_MD5_SIZE);
		}
		$params = [];
		foreach ($aliases as $values) {
			foreach ($values as $value) {
				$value = (string) $value;
				self::validateParameter($value);
				$params[] = $value;
			}
		}
		if (!$params) {
			return $name;
		}

		$params = '_' . implode(self::PARAM_DEL, $params);
		$fullName = $name . $params;
		if (strlen($fullName) > self::FOLDER_SIZE) {
			$fullName = $name . '_' . md5(substr($params, 1));
		}

		return $fullName;
	}

	/**
	 * Removes / from left and right side
	 *
	 * @param string|null $path
	 * @param bool $appendSlash
	 * @return string
	 */
	public static function normalizePath(?string $path, bool $appendSlash = true): string {
		$path = trim((string) $path, '/\\');

		return ($appendSlash && $path ? $path . '/' : $path);
	}

	protected static function validateParameter(string $value): void {
		if (!preg_match('#^[0-9a-zA-Z]+$#', $value)) {
			throw new \LogicException("Parameter '$value' has disallowed characters.");
		}
	}

}
