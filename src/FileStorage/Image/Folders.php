<?php

namespace WebChemistry\Images\FileStorage\Image;

use Nette\Utils\Image;
use WebChemistry\Images\Image\PropertyAccess;
use WebChemistry\Images\ImageStorageException;

abstract class Folders extends PropertyAccess {

	/** @var Image */
	private $imageClass;

	/** @var string */
	protected $assetsDir;

	/** @var string */
	private $wwwDir;

	/**
	 * @param string $wwwDir
	 * @param string $assetsDir
	 */
	public function __construct($wwwDir, $assetsDir) {
		$this->assetsDir = $assetsDir;
		$this->wwwDir = $wwwDir;
	}

	/**
	 * @return string
	 */
	protected function getAbsoluteAssetsDir() {
		if ($this->wwwDir && $this->assetsDir) {
			return $this->wwwDir . '/' . $this->assetsDir . '/';
		}
		if ($this->wwwDir) {
			return $this->wwwDir . '/';
		}
		if ($this->assetsDir) {
			return $this->assetsDir . '/';
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	protected function getAssetsDir() {
		return $this->assetsDir ? $this->assetsDir . '/' : NULL;
	}

	/**
	 * @return string
	 * @throws ImageStorageException
	 */
	public function getUploadPath() {
		if (!$this->isOriginal()) {
			throw new ImageStorageException('Given image is not original image.');
		}
		if (!$this->isOk()) {
			throw new ImageStorageException('Given image is not ok.');
		}
		$this->generateUniqueImageName();

		return $this->getAbsolutePath();
	}

	/**
	 * @return string
	 */
	public function getOriginalPath() {
		return $this->getAbsoluteAssetsDir() . $this->namespaceFolder() . self::ORIGINAL . '/' . $this->getNameWithPrefix();
	}

	/**
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->getAbsoluteAssetsDir() . $this->namespaceFolder() . $this->baseFolder() . '/' . $this->getNameWithPrefix();
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 *         \-------------------------------------------------------------------------------/
	 * @return string
	 */
	public function getRelativePath() {
		return $this->getAssetsDir() . $this->namespaceFolder() . $this->baseFolder() . '/' . $this->getNameWithPrefix();
	}

	/**
	 * Namespace directories
	 *
	 * @return string
	 */
	protected function namespaceFolder() {
		if ($this->getNamespace()) {
			return $this->getNamespace() . '/';
		}

		return NULL;
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 *                                         \-------------------------/
	 * @return string
	 */
	private function baseFolder() {
		return $this->sizeFolder() . $this->flagFolder() . $this->hashFolder();
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 *                                         \------/
	 * @return string
	 */
	private function sizeFolder() {
		$width = $this->getWidth();
		$height = $this->getHeight();

		if ($width && $height) {
			return $width . 'x' . $height;
		} else {
			if ($width) {
				return $width . 'x';
			} else {
				if ($height) {
					return 'x' . $height;
				}
			}
		}

		return self::ORIGINAL;
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 *                                                \-/
	 * @return string
	 */
	private function flagFolder() {
		if ($this->getFlag()) {
			return '_' . $this->getFlag();
		}

		return NULL;
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 *                                                   \----------------/
	 * @return string
	 */
	private function hashFolder() {
		if ($hash = $this->getHash()) {
			return '-' . $hash;
		}

		return NULL;
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 * \-----------------------------------------------------------------/
	 *
	 * @return string
	 */
	protected function getAbsoluteBasePath() {
		return $this->getAbsoluteAssetsDir() . $this->namespaceFolder() . $this->baseFolder();
	}

	/**
	 * %wwwDir%/%assetsDir%/namespace/namespace/120x150_8-1sa5fa5fs15afs61/prefix_._filename.jpg
	 * \---------------------------------------/
	 *
	 * @return string
	 */
	protected function getDeletePath() {
		return $this->getAbsoluteAssetsDir() . $this->namespaceFolder();
	}

	/************************* Others **************************/

	/**
	 * Create all directories recursive
	 */
	protected function createDirectories() {
		@mkdir($this->getAbsoluteBasePath(), 0777, TRUE); // @ - Directories may exist
	}

	public function generateUniqueImageName() {
		while ($this->isExists()) {
			$this->generatePrefix();
		}
	}

	/**
	 * @return array
	 */
	public function getImageSize() {
		return getimagesize($this->getAbsolutePath());
	}

	/**
	 * @return int
	 */
	public function getImageType() {
		$info = $this->getImageSize();

		return $info[2];
	}

	/**
	 * @return Image
	 */
	public function getNetteClass() {
		if (!$this->imageClass) {
			$this->imageClass = Image::fromFile($this->getAbsolutePath());
		}

		return $this->imageClass;
	}

	/**
	 * @return bool
	 */
	public function isExists() {
		return $this->imageClass || file_exists($this->getAbsolutePath()) && is_file($this->getAbsolutePath());
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getAbsoluteName();
	}

}
