<?php

namespace WebChemistry\Images\Resources;

use Nette\Utils\Random;
use WebChemistry\Images\Resources\Filters\ResourceFilter;

abstract class Resource implements IResource {

	/** @var string */
	protected $name;

	/** @var string */
	protected $prefix;

	/** @var string */
	protected $namespace;

	/** @var ResourceFilter[] */
	protected $filters = [];

	// immutables -- clones object

	/**
	 * @return static
	 */
	public function withFilters(array $filters) {
		$static = clone $this;
		$static->setFilters($filters);

		return $static;
	}

	/**
	 * @return static
	 */
	public function withFilter(string $filter, array $arguments = []) {
		$static = clone $this;
		$static->setFilter($filter, $arguments);

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

	/************************* Properties **************************/

	public function getSuffix(): ?string {
		$pos = strrpos($this->name, '.');
		if ($pos === false) {
			return null;
		}

		return substr($this->name, $pos + 1);
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

	public function hasFilters(): bool {
		return (bool) $this->filters;
	}

	/**
	 * @return ResourceFilter[]
	 */
	public function getFilters(): array {
		return $this->filters;
	}

	public function setFilter(string $filter, array $arguments = []) {
		$this->filters[$filter] = new ResourceFilter($filter, $arguments);

		return $this;
	}

	public function setFilterObject(ResourceFilter $filterCase) {
		$this->filters[$filterCase->getName()] = $filterCase;

		return $this;
	}

	/**
	 * @param ResourceFilter[] $filters
	 */
	public function setFilters(array $filters) {
		$this->filters = [];
		foreach ($filters as $filter) {
			$this->setFilterObject($filter);
		}
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
