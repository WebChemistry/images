<?php

namespace WebChemistry\Images\FileStorage\Image;

use Nette;
use WebChemistry\Images\ImageStorageException;

class Image extends Folders {

	/** @var Callback[] */
	public $onCreate = [];

	/** @var string */
	public $basePath;

	/** @var string */
	public $baseUri;

	/**
	 * @param string $wwwDir
	 * @param string $assetsDir
	 * @param string $absoluteName
	 * @param string $defaultImage
	 */
	public function __construct($wwwDir = NULL, $assetsDir = NULL, $absoluteName = NULL, $defaultImage = NULL) {
		parent::__construct($wwwDir, $assetsDir);

		$this->setAbsoluteName($absoluteName);
		$this->setDefaultImage($defaultImage);
	}

	/**
	 * @return bool
	 */
	public function delete() {
		$directory = $this->getDeletePath();
		if (!file_exists($directory)) {
			return FALSE;
		}

		$files = Nette\Utils\Finder::findFiles($this->getNameWithPrefix())->from($directory)->limitDepth(1);
		foreach ($files as $file) {
			@unlink($file);
		}

		return (bool) $files;
	}

	/**
	 * @param string $baseUri
	 * @return Image
	 */
	public function setBaseUri($baseUri) {
		$this->baseUri = $baseUri;

		return $this;
	}

	/**
	 * @param string $basePath
	 * @return Image
	 */
	public function setBasePath($basePath) {
		$this->basePath = $basePath;

		return $this;
	}

	private function getPathBefore() {
		if ($this->isBaseUri()) {
			return $this->baseUri;
		} else {
			return $this->basePath;
		}
	}

	/**
	 * @param Nette\Utils\Image $image
	 * @param string $imageType
	 * @throws ImageStorageException
	 */
	public function save(Nette\Utils\Image $image, $imageType = NULL) {
		$this->createDirectories();

		$image->save($this->getUploadPath(), $this->getQuality(), $imageType);
	}

	/**
	 * @param Nette\Http\FileUpload $image
	 * @param string $imageType
	 * @throws ImageStorageException
	 */
	public function saveUpload(Nette\Http\FileUpload $image, $imageType = NULL) {
		$this->createDirectories();

		$image->move($this->getUploadPath());
	}

	/**
	 * Generates new images from original
	 *
	 * @return string if image does not exist return const NO_IMAGE
	 */
	public function getLink() {
		$original = $this->getOriginalClass();

		// Original and resized image does not exist.
		if (!$this->isExists() && !$original->isExists()) {
			if ($this->getDefaultImage()) {
				return $this->getDefaultImageClass()->getLink();
			} else {
				return self::NO_IMAGE;
			}
		}

		// Resize image exists.
		if ($this->isExists()) {
			return $this->getPathBefore() . str_replace('%', '%25', $this->getRelativePath());
		}

		// Resize image does not exist
		if (!$this->isExists() && !$this->isOriginal()) {
			$image = $original->getNetteClass();
			$this->processHelpers($image);

			if ($this->getWidth() || $this->getHeight()) {
				$image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
			}

			foreach ($this->onCreate as $callback) {
				$callback($image);
			}

			$this->createDirectories();
			$image->save($this->getAbsolutePath(), $this->getQuality(), $this->getOriginalClass()->getImageType());

			return $this->getPathBefore() . str_replace('%', '%25', $this->getRelativePath());
		}
	}

}
