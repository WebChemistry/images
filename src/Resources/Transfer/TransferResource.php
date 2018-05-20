<?php

namespace WebChemistry\Images\Resources\Transfer;

use WebChemistry\Images\Resources\Resource;
use WebChemistry\Images\Resources\ResourceException;

abstract class TransferResource extends Resource implements ITransferResource {

	/** @var bool */
	private $saved = false;

	/**
	 * Image name
	 *
	 * @example image.png
	 * @param string $name
	 * @return self
	 * @throws \WebChemistry\Images\TypeException
	 */
	public function setName($name) {
		parent::setName($name);

		return $this;
	}

	/**
	 * Namespace for better structure
	 *
	 * @example first/second
	 * @param string $namespace
	 * @return self
	 * @throws ResourceException
	 * @throws \WebChemistry\Images\TypeException
	 */
	public function setNamespace($namespace) {
		parent::setNamespace($namespace);

		return $this;
	}

	/**
	 * Combination of name and namespace
	 *
	 * @example namespace/image.png
	 * @param string $id
	 * @return self
	 * @throws ResourceException
	 */
	public function setId($id) {
		$this->parseId($id);

		return $this;
	}

	/**
	 * @internal
	 * @throws ResourceException
	 */
	public function setSaved() {
		if ($this->saved) {
			throw new ResourceException('Cannot save same resource twice.');
		}
		$this->saved = true;
	}

}
