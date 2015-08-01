<?php

namespace WebChemistry\Images\Image;

use Nette, Nette\Utils\Strings, WebChemistry;

class PropertyAccess extends Nette\Object implements IImage {

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

	/** @var array */
	private $helpers = array();

	/** @var string */
	private $absoluteUrl;

	/** @var string */
	private $url;

	/** @var string */
	private $suffix;

	/** @var array */
	protected $useHelpers = array();

	/** @var string */
	protected $prefix;

	/**
	 * @return $this
	 */
	public function generatePrefix() {
		$this->setPrefix(Nette\Utils\Random::generate());

		return $this;
	}

	/************************* Getters **************************/

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return int
	 */
	public function getFlag() {
		return $this->flag;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name . '.' . $this->suffix;
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
	public function getAbsoluteUrl() {
		return $this->absoluteUrl;
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

	/************************* Setters **************************/

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * @param string $suffix
	 * @return $this
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;

		return $this;
	}

	/**
	 * @param string $absoluteUrl
	 * @return $this
	 */
	public function setAbsoluteUrl($absoluteUrl) {
		$this->absoluteUrl = $absoluteUrl;

		return $this;
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	public function setUrl($url) {
		if (Nette\Utils\Validators::isUrl($url)) {
			$this->url = $url;
		}

		return $this;
	}

	/**
	 * @param array $helpers
	 * @return $this
	 */
	public function setHelperClasses(array $helpers) {
		$this->helpers = $helpers;

		return $this;
	}

	/**
	 * @param string $string
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setMixedSize($string) {
		$explode = explode('|', $string);
		$this->setSize($explode[0]);

		if (count($explode) > 1) {
			$this->setHelpers(array_slice($explode, 1));
		}
	}

	/**
	 * @param array $parameters
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setHelpers(array $parameters) {
		foreach ($parameters as $parameter) {
			if (!preg_match('#([a-zA-Z]+)(:(.+))?#', $parameter, $matches)) {
				throw new WebChemistry\Images\ImageStorageException("Regular expresion '$parameter' is not valid.");
			}

			if (!isset($this->helpers[$matches[1]])) {
				throw new WebChemistry\Images\ImageStorageException("Helper '$matches[1]' is not exists.");
			}

			$this->useHelpers[] = array($this->helpers[$matches[1]], isset($matches[3]) ? $matches[3] : NULL);
		}
	}

	/**
	 * @param string $size
	 * @return $this
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
	 * @return $this
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
		//$this->name = end($explode);
		array_pop($explode);
		$this->namespace = $explode ? implode('/', $explode) : NULL;

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setName($name) {
		$name = $this->parseString($name);

		if (strpos($name, '/') !== FALSE) {
			throw new WebChemistry\Images\ImageStorageException('Name of image must not contain /');
		}

		$this->name = substr($name, 0, strrpos($name, '.'));
		$this->setSuffix(substr($name, strrpos($name, '.') + 1));

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setNameWithoutSuffix($name) {
		$this->name = $this->parseString($name);

		if (strpos($this->name, '/') !== FALSE) {
			throw new WebChemistry\Images\ImageStorageException('Name of image must not contain /');
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
	 * @return $this
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

		if ($this->namespace === Info::ORIGINAL) {
			throw new WebChemistry\Images\ImageStorageException('Namespace must not same name as original directory.');
		}

		return $this;
	}

	/**
	 * @param int $flag
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setIntegerFlag($flag) {
		if (!is_numeric($flag)) {
			throw new WebChemistry\Images\ImageStorageException('Flag muset be integer in PropertyAccess::setIntegerFlag');
		}

		$this->flag = (int) $flag;
	}

	/**
	 * @param string $flag
	 * @return $this
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function setFlag($flag) {
		$return = 0;

		foreach ((array) $flag as $row) {
			$return += $this->flagToInteger($row);
		}

		$this->flag = $return;

		return $this;
	}

	/**
	 * Set parameters [height, width, flag] from parent. If is null reset all parameters.
	 *
	 * @param PropertyAccess $parent
	 */
	public function setParent(PropertyAccess $parent = NULL) {
		if ($parent === NULL) {
			$parent = new self;
		}

		$this->setWidth($parent->getWidth());
		$this->setHeight($parent->getHeight());
		$this->setFlag($parent->getFlag());
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
}
