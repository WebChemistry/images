<?php

namespace WebChemistry\Images\Connectors;

use Nette;
use Nette\Http\FileUpload;
use WebChemistry\Images\Bridges\Nette\Image;
use WebChemistry\Images\Image\Content;
use WebChemistry\Images\Image\Delete;
use WebChemistry\Images\Image\Info;
use WebChemistry\Images\Image\Upload;

class Localhost extends Nette\Object implements IConnector {

	/** @var string */
	private $assetsDir;

	/** @var string */
	private $absoluteDir;

	/**
	 * @param string $absoluteDir
	 * @param string $dir
	 */
	public function __construct($absoluteDir, $dir) {
		$this->assetsDir = rtrim($dir, '/\\');
		$this->absoluteDir = rtrim($absoluteDir, '/\\');
	}

	/**
	 * @param FileUpload $upload
	 * @param string     $namespace
	 * @return Upload
	 */
	public function processUpload(FileUpload $upload, $namespace = NULL) {
		$image = new Upload($this, $upload);

		$image->setNamespace($namespace);

		return $image;
	}

	/**
	 * @param      $content
	 * @param      $name
	 * @param null $namespace
	 * @return Content
	 */
	public function processContent($content, $name, $namespace = NULL) {
		$image = new Content($this, $content);

		$image->setName($name);
		$image->setNamespace($namespace);

		return $image;
	}

	/**
	 * @param array  $helpers
	 * @param string $absoluteName
	 * @param null   $size
	 * @param null   $flag
	 * @param null   $noImage
	 * @return \WebChemistry\Images\Image\Image
	 */
	public function processGet(array $helpers, $absoluteName, $size = NULL, $flag = NULL, $noImage = NULL) {
		$image = new \WebChemistry\Images\Image\Image($this, $absoluteName, $noImage);

		$image->setHelperClasses($helpers);
		$image->setMixedSize($size);
		$image->setFlag($flag);

		return $image;
	}

	/**
	 * @param string $absoluteName
	 * @return mixed
	 */
	public function processDelete($absoluteName) {
		$image = new Delete($this);

		$image->setAbsoluteName($absoluteName);

		return $image;
	}

	public function setSettings(array $settings) {
	}

	/**
	 * @param Info $info
	 * @return string
	 */
	private function getPath(Info $info) {
		return $this->getBasePath($info) . '/' . $info->getNameWithPrefix();
	}

	/**
	 * @param Info $info
	 * @return string
	 */
	private function getBasePath(Info $info) {
		if ($info->namespaceFolder()) {
			$namespace = $info->namespaceFolder() . '/';
		} else {
			$namespace = '';
		}

		return $namespace . $info->getBaseFolder();
	}

	/**
	 * @param Info $info
	 */
	private function createDirectories(Info $info) {
		if ($info->namespaceFolder()) {
			$lastDir = $this->absoluteDir . '/';

			foreach (explode('/', $info->getNamespace()) as $namespace) {
				$lastDir .= $namespace . '/';

				@mkdir($lastDir);
			}
		}

		@mkdir($this->absoluteDir . '/' . $this->getBasePath($info)); // Original | resize dir
	}

	/**
	 * @param Info $info
	 * @return array
	 */
	public function getImageSize(Info $info) {
		return getimagesize($this->absoluteDir . '/' . $this->getPath($info));
	}

	/**
	 * @param Info $info
	 * @return Info
	 */
	public function getUniqueImageName(Info $info) {
		while ($this->isExists($info)) {
			$info->generatePrefix();
		}

		return $info;
	}

	/**
	 * @param Info $info
	 */
	public function delete(Info $info) {
		$path = $this->absoluteDir . '/' . $info->namespaceFolder();

		if (!file_exists($path)) {
			return;
		}

		$files = Nette\Utils\Finder::findFiles($info->getName())
								   ->from($path)
								   ->limitDepth(1);

		foreach ($files as $row) {
			@unlink((string) $row);
		}
	}

	/**
	 * @param Image  $image
	 * @param Info   $info
	 * @param string $imageType
	 */
	public function save(Image $image, Info $info, $imageType) {
		$this->createDirectories($info);

		$image->save($this->absoluteDir . '/' . $this->getPath($info), NULL, $imageType);
	}

	/**
	 * @param Info $info
	 * @return Nette\Utils\Image
	 * @throws Nette\Utils\UnknownImageFileException
	 */
	public function getNetteImage(Info $info) {
		return Image::fromFile($this->absoluteDir . '/' . $this->getPath($info));
	}

	/**
	 * @param Info $info
	 * @return string
	 */
	public function getLink(Info $info) {
		return $this->assetsDir . '/' . $this->getPath($info);
	}

	/**
	 * @param Info $info
	 * @return bool
	 */
	public function isExists(Info $info) {
		return file_exists($this->absoluteDir . '/' . $this->getPath($info));
	}
}