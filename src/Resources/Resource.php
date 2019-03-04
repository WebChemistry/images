<?php

namespace WebChemistry\Images\Resources;

use Nette\Utils\Random;
use WebChemistry\Images\Resources\Meta\TResourceMetaCache;

abstract class Resource implements IResource {

	use TResourceMetaCache;

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

	/** @var bool */
	protected $baseUrl = false;

	/** @var string|null */
	protected $defaultImage;

	// immutables -- clones object

	/**
	 * @return static
	 */
	public function withAliases(array $aliases) {
		$static = clone $this;
		$static->setAliases($aliases);

		return $static;
	}

	/**
	 * @return static
	 */
	public function withAlias(string $alias) {
		$static = clone $this;
		$static->setAlias($alias);

		return $static;
	}

	/**
	 * @return static
	 */
	public function withSuffix(string $suffix) {
		$static = clone $this;
		$static->setSuffix($suffix);

		return $static;
	}

	/**
	 * @return static
	 */
	public function withDefaultImage(?string $defaultImage) {
		$static = clone $this;
		$static->setDefaultImage($defaultImage);

		return $static;
	}

	/**
	 * @return static
	 */
	public function withBaseUrl(bool $baseUrl) {
		$static = clone $this;
		$static->setBaseUrl($baseUrl);

		return $static;
	}

	/************************* Properties **************************/

	/**
	 * @param string|null $defaultImage
	 */
	public function setDefaultImage(?string $defaultImage) {
		$this->defaultImage = $defaultImage;
	}

	public function setBaseUrl(bool $baseUrl = true) {
		$this->baseUrl = $baseUrl;
	}

	public function setSuffix(string $suffix): void {
		$this->name = pathinfo($this->name)['filename'] . '.' . $suffix;
	}

	protected function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * @param null|string $namespace
	 * @throws ResourceException
	 */
	protected function setNamespace(?string $namespace) {
		if (!$namespace) {
			$namespace = null;

			return;
		}

		if (!preg_match('#^[\w/-]+$#', $namespace)) {
			throw new ResourceException('Namespace \'' . $namespace . '\' is not valid.');
		}

		$this->namespace = trim($namespace, '/');
	}

	public function generatePrefix(int $length = 10): void {
		$this->prefix = Random::generate($length);
	}

	/////////////////////////////////////////////////////////////////

	/**
	 * @deprecated use hasAliases() instead
	 * @return bool
	 */
	public function toModify(): bool {
		return $this->hasAliases();
	}

	public function hasAliases(): bool {
		return (bool) $this->aliases;
	}

	public function getAliases(): array {
		return $this->aliases;
	}

	public function setAlias(string $alias, array $args = []): void {
		$this->aliases[$alias] = $args;
	}

	public function setAliases(array $aliases): void {
		$this->aliases = $aliases;
	}

	/**
	 * @param string $id
	 * @throws ResourceException
	 */
	protected function parseId(string $id): void {
		$explode = explode('/', $id);
		$count = count($explode);

		$this->setName($explode[$count - 1]);
		if ($count !== 1) {
			$this->setNamespace(implode('/', array_slice($explode, 0, $count - 1)));
		}
	}

	/////////////////////////////////////////////////////////////////

	public function isEmpty(): bool {
		return !$this->name;
	}

	/**
	 * Combination of namespace and name
	 *
	 * @return string
	 */
	public function getId(): string {
		return ($this->namespace ? $this->namespace . '/' : '') . $this->getName();
	}

	public function getDefaultImage(): ?string {
		return $this->defaultImage;
	}

	public function isBaseUrl(): bool {
		return $this->baseUrl;
	}

	public function getName(): string {
		return ($this->prefix ? $this->prefix . self::PREFIX_SEP : '') . $this->name;
	}

	public function getRawName(): string {
		return $this->name;
	}

	public function getNamespace(): ?string {
		return $this->namespace;
	}

	public function getPrefix(): ?string {
		return $this->prefix;
	}

	public function __toString(): string {
		return $this->getId();
	}

}
