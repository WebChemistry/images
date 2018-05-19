<?php

namespace WebChemistry\Images\Resources;

use Nette\Utils\Image;
use Nette\Utils\Random;
use WebChemistry\Images\Helpers;
use WebChemistry\Images\TypeException;

abstract class Resource implements IResource {

	const PREFIX_SEP = '_._';

	/** @var array */
	public $additional = [];

	/** @var string */
	protected $name;

	/** @var string */
	protected $prefix;

	/** @var string */
	protected $namespace;

	/** @var array */
	protected $aliases = [];

	/** @var int|null */
	protected $width;

	/** @var int|null */
	protected $height;

	/** @var int */
	protected $sizeFlag = Image::FIT;

	/************************* Properties **************************/

	/**
	 * @param int|null $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}

	/**
	 * @param int|null $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}

	/**
	 * @param int|null $width
	 * @param int|null $height
	 * @param int $flag
	 */
	public function setSize($width, $height, $flag = Image::FIT) {
		$this->width = $width;
		$this->height = $height;
		$this->sizeFlag = $flag;
	}

	/**
	 * @return int|null
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return int|null
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @return array
	 */
	public function getSize() {
		return [$this->width, $this->height, $this->sizeFlag];
	}

	/**
	 * @param string $suffix
	 */
	public function setSuffix($suffix) {
		$this->name = pathinfo($this->name)['filename'] . '.' . $suffix;
	}

	/**
	 * @param string $name
	 * @throws TypeException
	 */
	protected function setName($name) {
		if ($name && !is_string($name)) {
			throw new TypeException('string', $name);
		}

		$this->name = $name;
	}

	/**
	 * @param string $namespace
	 * @throws ResourceException
	 * @throws TypeException
	 */
	protected function setNamespace($namespace) {
		if ($namespace !== null && !is_string($namespace)) {
			throw new TypeException('nullable string', $namespace);
		}
		if ($namespace && !preg_match('#^[\w/-]+$#', $namespace)) {
			throw new ResourceException('Namespace \'' . $namespace . '\' is not valid.');
		}

		$this->namespace = $namespace ? trim($namespace, '/') : null;
	}

	/**
	 * @param int $length
	 */
	public function generatePrefix($length = 10) {
		$this->prefix = Random::generate($length);
	}

	/////////////////////////////////////////////////////////////////

	/**
	 * @return bool
	 */
	public function toModify() {
		return (bool) $this->aliases;
	}

	/**
	 * @return array
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * @param string $alias
	 */
	public function setAlias($alias, array $args = []) {
		$this->aliases[$alias] = $args;
	}

	/**
	 * @param array $aliases
	 */
	public function setAliases(array $aliases) {
		$this->aliases = $aliases;
	}

	/**
	 * @param string $id
	 * @throws ResourceException
	 */
	protected function parseId($id) {
		if ($id && !is_string($id)) {
			throw new ResourceException('Identifier must be string.');
		}
		$explode = explode('/', $id);
		$count = count($explode);

		$this->setName($explode[$count - 1]);
		if ($count !== 1) {
			$this->setNamespace(implode('/', array_slice($explode, 0, $count - 1)));
		}
	}

	/////////////////////////////////////////////////////////////////

	/**
	 * Combination of namespace and name
	 *
	 * @return string
	 */
	public function getId() {
		return ($this->namespace ? $this->namespace . '/' : '') . $this->getName();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return ($this->prefix ? $this->prefix . self::PREFIX_SEP : '') . $this->name;
	}

	/**
	 * @return string
	 */
	public function getRawName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	public function __toString() {
		return $this->getId();
	}

}
