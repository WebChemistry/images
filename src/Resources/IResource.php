<?php

namespace WebChemistry\Images\Resources;

interface IResource {

	const PREFIX_SEP = '_._';
	// deprecated
	const ORIGINAL = 'original';

	/**
	 * @deprecated use hasAliases() instead
	 * @return bool
	 */
	public function toModify(): bool;

	public function hasAliases(): bool;

	/**
	 * Name with prefix and namespace
	 *
	 * @return string
	 */
	public function getId(): string;

	public function getPrefix(): ?string;

	public function getName(): string;

	public function getDefaultImage(): ?string;

	public function isBaseUrl(): bool;

	public function isEmpty(): bool;

	/**
	 * Name without prefix
	 *
	 * @return string
	 */
	public function getRawName(): string;

	public function getNamespace(): ?string;

	public function generatePrefix(int $length = 10): void;

	public function getAliases(): array;

	public function setAliases(array $aliases);

	public function setAlias(string $alias);

	public function setSuffix(string $suffix);

	public function setDefaultImage(?string $defaultImage);

	public function setBaseUrl(bool $baseUrl = true);

	// immutables -- clones object

	/**
	 * @return static
	 */
	public function withAliases(array $aliases);

	/**
	 * @return static
	 */
	public function withAlias(string $alias);

	/**
	 * @return static
	 */
	public function withSuffix(string $suffix);

	/**
	 * @return static
	 */
	public function withDefaultImage(?string $defaultImage);

	/**
	 * @return static
	 */
	public function withBaseUrl(bool $baseUrl);

}
