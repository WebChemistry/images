<?php

namespace WebChemistry\Images\Resources;


class FileResource extends Resource implements IFileResource {

	/**
	 * @param string $id
	 * @throws ResourceException
	 */
	public function __construct($id) {
		$this->parseId($id);
	}

	/**
	 * @return self
	 * @throws ResourceException
	 */
	public function getOriginal() {
		return new self($this->getId());
	}

}
