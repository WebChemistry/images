<?php

namespace WebChemistry\Images;

use Nette\Http\FileUpload;
use Nette\Utils\Callback;
use Nette\Utils\Image;
use WebChemistry\Images\Helpers\IHelper;
use WebChemistry\Images\Image\PropertyAccess;

abstract class AbstractStorage {

	/** @var string */
	protected $defaultImage;

	/** @var array */
	protected $helpers;

	/** @var array */
	protected $settings;

	/**
	 * @param string $defaultImage
	 * @param array $settings
	 * @throws ImageStorageException
	 */
	public function __construct($defaultImage, array $settings) {
		$this->defaultImage = $defaultImage;
		$this->settings = $settings;
		$this->helpers = $this->extractHelpers($settings['helpers']);
	}

	/**
	 * @param array $helpers
	 * @return array
	 * @throws ImageStorageException
	 */
	private function extractHelpers(array $helpers) {
		foreach ($helpers as $name => $class) {
			if (!is_object($class)) {
				$helpers[$name] = new $class;
			} else {
				$helpers[$name] = $class;
			}

			if (!$helpers[$name] instanceof IHelper) {
				throw new ImageStorageException("Helper '$name' must be instance of WebChemistry\\Images\\Helpers\\IHelper");
			}
		}

		return $helpers;
	}

	/**
	 * @return PropertyAccess
	 */
	abstract public function createImage();

	/**
	 * @param string $absoluteName
	 * @param string $size
	 * @param string|int $flag
	 * @param string $defaultImage
	 * @param callable $callback
	 * @return PropertyAccess
	 */
	public function get($absoluteName, $size = NULL, $flag = NULL, $defaultImage = NULL, $callback = NULL) {
		$image = $this->createImage();
		if ($defaultImage) {
			$image->setDefaultImage($defaultImage);
		}
		$image->setAbsoluteName($absoluteName);
		$image->setMixedSize($size);
		$image->setFlag($flag);
		if ($callback) {
			Callback::check($callback);
			$callback($image);
		}

		return $image;
	}

	/**
	 * @param FileUpload $fileUpload
	 * @param string $namespace
	 * @param callable $callback
	 * @return string Absolute name
	 */
	public function saveUpload(FileUpload $fileUpload, $namespace = NULL, $callback = NULL) {
		if (!$fileUpload->isOk() || !$fileUpload->isImage()) {
			return NULL;
		}

		$image = $this->createImage();
		$image->setNamespace($namespace);
		$image->setName($fileUpload->getSanitizedName());
		if ($callback) {
			Callback::check($callback);
			$callback($image);
		}

		$image->saveUpload($fileUpload);

		return (string) $image;
	}

	/**
	 * @param Image $image
	 * @param string $fileName
	 * @param string $namespace
	 * @param callable $callback
	 * @return string AbsoluteName
	 */
	public function saveImage(Image $image, $fileName, $namespace = NULL, $callback = NULL) {
		$newImage = $this->createImage();
		$newImage->setName($fileName);
		$newImage->setNamespace($namespace);
		if ($callback) {
			Callback::check($callback);
			$callback($newImage);
		}

		$newImage->save($image);

		return (string) $newImage;
	}

	/**
	 * @param string $absoluteName
	 * @return bool
	 */
	public function delete($absoluteName) {
		if (!is_string($absoluteName)) {
			return FALSE;
		}

		$image = $this->createImage();
		$image->setAbsoluteName($absoluteName);

		return $image->delete();
	}

}
