<?php

namespace WebChemistry\Images\Resources;


class FileResource extends Resource implements IFileResource {

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		$this->parseId($id);
	}

	/**
	 * @return self
	 */
	public function getOriginal() {
		return new self($this->getId());
	}

}
