<?php

namespace WebChemistry\Images\Image;

use Nette;
use Nette\Utils\Strings;
use WebChemistry;

/**
 * @property string $name
 * @property string $namespace
 * @property int|string $height
 * @property int|string $width
 * @property int $flag
 * @property string $suffix
 * @property int $quality
 * @property string $prefix
 * @property string $absoluteName
 * @property-read string $nameWithPrefix
 */
abstract class PropertyAccess extends Nette\Object {

	const PREFIX_SEP = '_._';

	const ORIGINAL = 'original';

	const NO_IMAGE = '#noimage';

	/** @var string */
	private $name;

	/** @var string */
	private $namespace;

	/** @var int|string */
	private $height;

	/** @var int|string */
	private $width;

	/** @var int */
	private $flag = 0;

	/** @var bool */
	private $baseUri = FALSE;

	/** @var array Registered helpers */
	private $helpers = [];

	/** @var string */
	private $suffix;

	/** @var array [class, parameters] */
	protected $useHelpers = [];

	/** @var string */
	protected $prefix;

	/** @var int */
	private $quality;

	/** @var string */
	private $defaultImage;

	/**
	 * @return PropertyAccess
	 */
	public function generatePrefix() {
		$this->setPrefix(Nette\Utils\Random::generate());

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isOk() {
		return (bool) $this->getName();
	}

	/**
	 * @return bool
	 */
	public function isOriginal() {
		return !$this->getWidth() && !$this->getHeight() && !$this->getHash();
	}

	/**
	 * @return PropertyAccess|static
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function getDefaultImageClass() {
		if (!$this->defaultImage) {
			throw new WebChemistry\Images\ImageStorageException("Default image name does not exist.");
		}

		$clone = clone $this;

		$clone->setAbsoluteName($this->defaultImage);
		$clone->setPrefix(NULL);
		$clone->setDefaultImage(NULL);

		return $clone;
	}

	/**
	 * @return PropertyAccess|static
	 */
	public function getOriginalClass() {
		$original = clone $this;
		$original->setWidth(NULL)
			->setHeight(NULL)
			->setFlag(NULL)
			->parseHelpers([]);

		return $original;
	}

	/************************* Helpers **************************/

	/**
	 * @return string
	 */
	protected function getHash() {
		if (!$this->useHelpers) {
			return NULL;
		}

		$hash = NULL;

		foreach ($this->useHelpers as $parameters) {
			$hash .= preg_replace('#\s+#', '', (is_object($parameters[0]) ? get_class($parameters[0]) : $parameters[0])) . preg_replace('#\s+#', '', $parameters[1]);
		}

		return md5($hash);
	}

	/**
	 * @param WebChemistry\Images\Helpers\IHelper $helper
	 * @param string $name
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function addHelper(WebChemistry\Images\Helpers\IHelper $helper, $name) {
		if (isset($this->helpers[$name])) {
			throw new WebChemistry\Images\ImageStorageException("Helper '$name' already exists.");
		}
		$this->helpers[$name] = $helper;

		return $this;
	}

	/**
	 * @param string $parameter
	 * @return array
	 */
	private function formatParameter($parameter) {
		if (!$parameter) {
			return [];
		}

		return array_map(function ($value) {
			return trim($value);
		}, explode(',', $parameter));
	}

	/**
	 * @param Nette\Utils\Image $image
	 */
	protected function processHelpers(Nette\Utils\Image $image) {
		foreach ($this->useHelpers as $parameters) {
			/** @var WebChemistry\Images\Helpers\IHelper $class */
			list($class, $parameter) = $parameters;

			$class->invoke($image, $this->formatParameter($parameter));
		}
	}

	/**
	 * @param array $parameters
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function parseHelpers(array $parameters) {
		if (!$parameters) {
			$this->useHelpers = [];
		}

		foreach ($parameters as $parameter) {
			if (!preg_match('#([a-zA-Z]+)(:(.+))?#', $parameter, $matches)) {
				throw new WebChemistry\Images\ImageStorageException("Regular expression '$parameter' is not valid.");
			}

			if (!isset($this->helpers[$matches[1]])) {
				throw new WebChemistry\Images\ImageStorageException("Helper '$matches[1]' is not exists.");
			}

			$this->useHelpers[] = array($this->helpers[$matches[1]], isset($matches[3]) ? $matches[3] : NULL);
		}
	}

	/************************* Setters **************************/

	/**
	 * @param string $prefix
	 * @return PropertyAccess
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * @param string $suffix
	 * @return PropertyAccess
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;

		return $this;
	}

	/**
	 * @param string $string
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setMixedSize($string) {
		$explode = explode('|', $string);
		$this->setSize($explode[0]);

		if (count($explode) > 1) {
			$this->parseHelpers(array_slice($explode, 1));
		}

		return $this;
	}

	/**
	 * @param string $size
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setSize($size) {
		$explode = explode('x', $this->parseString($size));

		if (count($explode) > 2) {
			throw new WebChemistry\Images\ImageStorageException('Size have more than 2 sizes.');
		}

		if (count($explode) === 2) {
			$this->width = strpos($explode[0], '%') === FALSE ? $this->checkNum($explode[0]) : $explode[0];
			$this->height = strpos($explode[1], '%') === FALSE ? $this->checkNum($explode[1]) : $explode[1];
		} else {
			$this->width = strpos($explode[0], '%') === FALSE ? $this->checkNum($explode[0]) : $explode[0];
			$this->height = NULL;
		}

		return $this;
	}

	/**
	 * @param string $name
	 * @return PropertyAccess
	 */
	public function setAbsoluteName($name) {
		if (!$name) {
			return $this;
		}

		if (Strings::startsWith($name, '//')) {
			$this->baseUri = TRUE;
		}

		$name = $this->parseString($name);

		$explode = explode('/', $name);

		$this->setName(end($explode));
		array_pop($explode);
		$this->namespace = $explode ? implode('/', $explode) : NULL;

		return $this;
	}

	/**
	 * @param string $name
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setName($name) {
		$name = $this->parseString($name);

		if (strpos($name, '/') !== FALSE || strpos($this->name, '\\') !== FALSE) {
			throw new WebChemistry\Images\ImageStorageException(printf('Image name must not contain / or \. Given
			%s.', $name));
		} else if (strpos($name, '.') === FALSE) {
			throw new WebChemistry\Images\ImageStorageException('Name must contain dot. Please use
			method setNameWithoutSuffix.');
		}

		$this->name = substr($name, 0, strrpos($name, '.'));
		$this->setSuffix(substr($name, strrpos($name, '.') + 1));

		return $this;
	}

	/**
	 * @param string $name
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setNameWithoutSuffix($name) {
		$this->name = $this->parseString($name);

		if (strpos($this->name, '/') !== FALSE || strpos($this->name, '\\') !== FALSE) {
			throw new WebChemistry\Images\ImageStorageException(printf('Image name must not contain / or \. Given
			%s.', $name));
		}

		return $this;
	}

	/**
	 * @param int|string $height
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setHeight($height) {
		$this->checkNum($height);
		$this->height = $height;

		return $this;
	}

	/**
	 * @param int|string $width
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setWidth($width) {
		$this->checkNum($width);
		$this->width = $width;

		return $this;
	}

	/**
	 * @param string $namespace
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setNamespace($namespace) {
		$this->namespace = $this->parseString($namespace);

		if ($this->namespace === self::ORIGINAL) {
			throw new WebChemistry\Images\ImageStorageException('Namespace must not same as original directory.');
		}

		return $this;
	}

	/**
	 * @param int $flag
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setIntegerFlag($flag) {
		if (!is_numeric($flag)) {
			throw new WebChemistry\Images\ImageStorageException('Flag muset be integer in PropertyAccess::setIntegerFlag');
		}

		$this->flag = (int) $flag;

		return $this;
	}

	/**
	 * @param string $flag
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setFlag($flag) {
		if ($flag === NULL) {
			$this->flag = NULL;

			return $this;
		}

		$return = 0;

		foreach ((array) $flag as $row) {
			if (!is_numeric($row)) {
				$return += $this->flagToInteger($row);
			} else {
				$return += $row;
			}
		}

		$this->flag = $return;

		return $this;
	}

	/**
	 * @param int $quality
	 * @return PropertyAccess
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setQuality($quality) {
		if (!is_int($quality)) {
			throw new WebChemistry\Images\ImageStorageException(printf('Parameter quality must be integer, %s given.',
				gettype($quality)));
		} else if (!Nette\Utils\Validators::isInRange($quality, [0, 100])) {
			throw new WebChemistry\Images\ImageStorageException('Quality must be value in range 0 - 100.');
		}

		$this->quality = $quality;

		return $this;
	}

	/**
	 * @param string $defaultImage
	 * @return PropertyAccess
	 */
	public function setDefaultImage($defaultImage) {
		$this->defaultImage = $defaultImage;

		return $this;
	}

	/************************* Getters **************************/

	/**
	 * @return string
	 */
	public function getNameWithPrefix() {
		$prefix = $this->getPrefix();

		return ($prefix ? $prefix . self::PREFIX_SEP : NULL) . $this->getName();
	}

	/**
	 * @return string
	 */
	public function getAbsoluteName() {
		$namespace = $this->getNamespace();

		return ($namespace ? $namespace . '/' : '') . $this->getNameWithPrefix();
	}

	/**
	 * @return int|null
	 */
	public function getFlag() {
		return $this->flag;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name . ($this->suffix ? '.' . $this->suffix : '');
	}

	/**
	 * @return string
	 */
	public function getNameWithoutSuffix() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @return int|string
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @return int|string
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return bool
	 */
	public function isBaseUri() {
		return $this->baseUri;
	}

	/**
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @return int
	 */
	public function getQuality() {
		return $this->quality;
	}

	/**
	 * @return string
	 */
	public function getDefaultImage() {
		return $this->defaultImage;
	}

	/************************* Others **************************/

	/**
	 * @param string $flag
	 * @return mixed
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	private function flagToInteger($flag) {
		$flag = trim(Strings::upper($flag));

		$value = @constant('Nette\Utils\Image::' . $flag);

		if ($value === NULL) {
			throw new WebChemistry\Images\ImageStorageException("WebChemistry\\Images: Flag '$flag' does not exist in Nette\\Utils\\Image.");
		}

		return $value;
	}

	/**
	 * @param int $num
	 * @return int
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	private function checkNum($num) {
		$parse = rtrim($num, '%');

		if ($parse && !is_numeric($parse)) {
			throw new WebChemistry\Images\ImageStorageException('Height and width must be integer or percent.');
		}

		return $parse ? (int) $parse : NULL;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	private function parseString($str) {
		return trim(trim($str), '/');
	}

	/**
	 * @return string
	 */
	abstract public function getAbsolutePath();

	/**
	 * @return string
	 */
	abstract public function getRelativePath();

	/**
	 * @return bool
	 */
	abstract public function delete();

	/**
	 * @return string
	 */
	abstract public function getLink();

	/**
	 * @return bool
	 */
	abstract public function isExists();

	/**
	 * @param \Nette\Utils\Image $image
	 * @param int $imageType
	 * @return PropertyAccess|static
	 */
	abstract public function save(Nette\Utils\Image $image, $imageType = NULL);

	/**
	 * @param Nette\Http\FileUpload $image
	 * @param int $imageType
	 * @return PropertyAccess|static
	 */
	abstract public function saveUpload(Nette\Http\FileUpload $image, $imageType = NULL);

}
