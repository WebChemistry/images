<?php

namespace WebChemistry\Images\Resources;

interface IResource {

	const ORIGINAL = 'original';

	/**
	 * @return bool
	 */
	public function toModify(): bool;

	/**
	 * Name with prefix and namespace
	 *
	 * @return string
	 */
	public function getId(): string;

	public function getPrefix(): ?string;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string Name without prefix
	 */
	public function getRawName(): string;

	public function getNamespace(): ?string;

	/**
	 * @param int $length
	 */
	public function generatePrefix(int $length = 10): void;

	/**
	 * @return array
	 */
	public function getAliases(): array;

	/**
	 * @param array $aliases
	 */
	public function setAliases(array $aliases): void;

	/**
	 * @param string $alias
	 */
	public function setAlias(string $alias);

}
