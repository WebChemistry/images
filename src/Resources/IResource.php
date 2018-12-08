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

}
