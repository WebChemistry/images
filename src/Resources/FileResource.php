<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

class FileResource extends Resource implements IFileResource {

	/**
	 * @param string $id
	 * @throws ResourceException
	 */
	public function __construct(string $id) {
		$this->parseId($id);
	}

	/**
	 * @return self
	 * @throws ResourceException
	 */
	public function getOriginal(): IFileResource {
		return new self($this->getId());
	}

}
