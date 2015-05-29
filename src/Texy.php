<?php

namespace WebChemistry\Images;

class Texy {

	const NAMESPACE_REGEX = '([^\\?%*:|"<>]+)';

	const SIZE_REGEX = '([0-9x%]+)';

	const FLAG_REGEX = '(\w+)';

	/**
	 * @param \Texy   $texy
	 * @param Storage $storage
	 * @param string  $basePath
	 * @param string  $baseUri
	 */
	public static function register(\Texy $texy, Storage $storage, $basePath = NULL, $baseUri = NULL) {
		/** [img absName, size, flag]:(attr=value) */
		$texy->registerLinePattern(function (\TexyLineParser $parser, array $matches, $name) use ($storage, $basePath, $baseUri) {
			$last = end($matches);
			$attrs = array();

			if (preg_match('#:\(([^)]+)\)#', $last, $match)) {
				unset($matches[count($matches) - 1]);

				foreach (explode(',', $match[1]) as $value) {
					if (strpos($value, '=') === FALSE) {
						continue;
					}

					$args = explode('=', trim($value));

					if ($args[0] === 'src') {
						continue;
					}

					$attrs[trim($args[0])] = trim($args[1]);
				}
			}

			foreach ($matches as $key => $value) {
				if ($key % 2 == 0 || empty($value)) {
					unset($matches[$key]);
				}
			}

			$image = call_user_func_array(array($storage, 'get'), $matches);

			$el = \TexyHtml::el('img', array(
					'src' => ($image->isBaseUri() ? $basePath : $baseUri) . '/' . $image->getLink()
				) + $attrs);

			return $el;
		}, '#\[img ' . self::NAMESPACE_REGEX . '(,\s*' . self::SIZE_REGEX . '(,\s*' . self::FLAG_REGEX . ')?)?](:\([^)(]+\))??#U', 'imageStorage');
	}
}
