<?php

namespace WebChemistry\Images\Resources;

use WebChemistry\Images\Resources\Filters\ResourceFilter;

interface IResource {

	public const PREFIX_SEP = '_._';

	/**
	 * Name with prefix and namespace
	 */
	public function getId(): string;

	public function getPrefix(): ?string;

	public function getName(): string;

	public function isEmpty(): bool;

	/**
	 * Name without prefix
	 */
	public function getRawName(): string;

	public function getNamespace(): ?string;

	public function getSuffix(): ?string;

	public function generatePrefix(int $length = 10): void;

	/**
	 * @return ResourceFilter[]
	 */
	public function getFilters(): array;

	/**
	 * @param ResourceFilter[] $filters
	 */
	public function setFilters(array $filters);

	public function setFilter(string $filter, array $arguments = []);

	public function setSuffix(string $suffix);

	// immutables -- clones object

	/**
	 * @param ResourceFilter[] $filters
	 * @return static
	 */
	public function withFilters(array $filters);

	/**
	 * @return static
	 */
	public function withFilter(string $filter, array $arguments = []);

	/**
	 * @return static
	 */
	public function withSuffix(string $suffix);

}
