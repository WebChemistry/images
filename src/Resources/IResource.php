<?php

namespace WebChemistry\Images\Resources;

interface IResource {

	/**
	 * @return bool
	 */
	public function toModify();

	/**
	 * Name with prefix and namespace
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getPrefix();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string Name without prefix
	 */
	public function getRawName();

	/**
	 * @return string
	 */
	public function getNamespace();

	/**
	 * @param int $length
	 */
	public function generatePrefix($length = 10);

	/**
	 * @return array
	 */
	public function getAliases();

	/**
	 * @param array $aliases
	 */
	public function setAliases(array $aliases);

	/**
	 * @param string $alias
	 */
	public function setAlias($alias);

}
