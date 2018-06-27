<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use WebChemistry\Images\Resources\Resource;
use WebChemistry\Images\Resources\ResourceException;

abstract class TransferResource extends Resource implements ITransferResource {

	/** @var bool */
	private $saved = false;

	/**
	 * Combination of name and namespace
	 *
	 * @example namespace/image.png
	 * @param string $id
	 * @return self
	 * @throws ResourceException
	 */
	public function setId(string $id) {
		$this->parseId($id);

		return $this;
	}

	public function setNamespace(?string $namespace) {
		return parent::setNamespace($namespace);
	}

	public function setName(string $name): void {
		parent::setName($name);
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
