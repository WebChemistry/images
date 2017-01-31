<?php

namespace WebChemistry\Images\Resources;


class FileResource extends Resource implements IFileResource {

	/** @var bool */
	protected $baseUri = FALSE;

	/** @var string */
	protected $defaultImage = NULL;

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		$this->parseId($id);
	}

	/**
	 * @param bool $baseUri
	 */
	public function setBaseUri($baseUri = TRUE) {
		$this->baseUri = $baseUri;
	}

	/**
	 * @return bool
	 */
	public function isBaseUri() {
		return $this->baseUri;
	}

	/**
	 * @param string $defaultImage
	 */
	public function setDefaultImage($defaultImage) {
		$this->defaultImage = $defaultImage;
	}

	/**
	 * @return string
	 */
	public function getDefaultImage() {
		return $this->defaultImage;
	}

	/**
	 * @return self
	 */
	public function getOriginal() {
		return new self($this->getId());
	}

}
