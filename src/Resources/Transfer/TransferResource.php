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
	 */
	public function setId($id) {
		$this->parseId($id);

		return $this;
	}

	/**
	 * @internal
	 */
	public function setSaved() {
		if ($this->saved) {
			throw new ResourceException('Cannot save same resource twice.');
		}
		$this->saved = true;
	}

}
