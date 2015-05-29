<?php

namespace WebChemistry\Images;

use Nette;
use WebChemistry\Images\Connectors;
use WebChemistry\Images\Image;

class Storage extends Nette\Object {

	/** @var string */
	protected $noImage;

	/** @var array */
	protected $settings = array();

	/** @var string */
	protected $namespace;

	/** @var Connectors\IConnector */
	protected $connector;

	/** @var Connectors\Manager */
	protected $connectorManager;

	/**
	 * @param string             $noImage
	 * @param array              $settings
	 * @param Connectors\Manager $connectorManager
	 * @throws ImageStorageException
	 */
	public function __construct($noImage, array $settings, Connectors\Manager $connectorManager) {
		$this->noImage = $noImage;
		$this->settings = $settings;
		$this->connector = $connectorManager->getConnector('default');
		$this->connectorManager = $connectorManager;
	}

	/**
	 * @param string $namespace
	 * @return NamespaceStorage
	 */
	public function setNamespace($namespace) {
		return new NamespaceStorage($this->noImage, $this->settings, $this->connectorManager, $namespace);
	}

	/**
	 * @param string $connector
	 * @return ConnectorStorage
	 */
	public function setConnector($connectorName) {
		return new ConnectorStorage($this->noImage, $this->settings, $this->connectorManager, $connectorName);
	}

	/**
	 * @param Nette\Http\FileUpload $upload
	 * @param string                $namespace
	 * @param bool                  $save FALSE Return image without saving
	 * @return NULL|Image\Upload
	 * @throws ImageStorageException
	 */
	public function saveUpload(Nette\Http\FileUpload $upload, $namespace = NULL, $save = TRUE) {
		if (!$upload->isOk()) {
			return NULL;
		}

		$namespace = $namespace ? $namespace : $this->namespace;

		$image = $this->connector->processUpload($upload, $namespace);

		if (!$image instanceof Image\Upload) {
			throw new ImageStorageException('Connector::processUpload must return WebChemistry\Images\Image\Upload');
		}

		if ($save === TRUE) {
			$image->save();
		}

		return $image;
	}

	/**
	 * Save image from string
	 *
	 * @param string $content
	 * @param string $name
	 * @param null   $namespace
	 * @param bool   $save FALSE Return image without saving
	 * @return Image\Content
	 * @throws ImageStorageException
	 */
	public function saveContent($content, $name, $namespace = NULL, $save = TRUE) {
		$namespace = $namespace ? $namespace : $this->namespace;

		$image = $this->connector->processContent($content, $name, $namespace);

		if (!$image instanceof Image\Content) {
			throw new ImageStorageException('Connector::processContent must return WebChemistry\Images\Image\Content');
		}

		if ($save === TRUE) {
			$image->save();
		}

		return $image;
	}

	public function delete($absoluteName) {
		if (!is_string($absoluteName) || !$absoluteName) {
			return;
		}

		$image = $this->connector->processDelete($absoluteName);

		if (!$image instanceof Image\Delete) {
			throw new ImageStorageException('Connector::processDelete must return WebChemistry\Images\Image\Delete');
		}

		$image->delete();
	}

	/************************* Getters **************************/

	public function getLink($absoluteName, $size = NULL, $flag = NULL, $noImage = NULL) {
		return $this->get($absoluteName, $size, $flag, $noImage)
					->getLink();
	}

	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param      $absoluteName
	 * @param null $size
	 * @param null $flag
	 * @param null $noImage
	 * @return mixed|Image\Image
	 * @throws ImageStorageException
	 */
	public function get($absoluteName, $size = NULL, $flag = NULL, $noImage = NULL) {
		if (preg_match('#^([a-zA-z]+):(.+)#', $absoluteName, $matches) && !Nette\Utils\Strings::startsWith($matches[1], 'http')) {
			/** @var ConnectorStorage $storage */
			$storage = $this->setConnector($matches[1]);

			return $storage->get($matches[2], $size, $flag, $noImage);
		}

		$noImage = $noImage ? $noImage : $this->noImage;

		$class = $this->connector->processGet($this->settings['helpers'], $absoluteName, $size, $flag, $noImage);

		if (!$class instanceof Image\Image) {
			throw new ImageStorageException('Connector::processGet must return WebChemistry\Images\Image\Image');
		}

		return $class;
	}

	/************************* Deprecated **************************/

	/**
	 * @deprecated
	 * @return NamespaceStorage
	 */
	public function createNamespace($namespace) {
		trigger_error('createNamespace is deprecated, please use setNamespace');

		return new NamespaceStorage($this->noImage, $this->settings, $namespace, $this->connectors);
	}

	/**
	 * @deprecated
	 */
	public function create($absoluteName, $size = NULL, $flag = NULL, $noImage = NULL) {
		trigger_error('create is deprecated, please use get');
		$image = new Image\Image($this->assetsDir, $absoluteName, $noImage ? $noImage : $this->noImage, $this->basePath);

		$image->setSize($size);
		$image->setFlag($flag);

		return $image;
	}

	/**
	 * @deprecated
	 */
	public function fromContent($content, $name, $namespace = NULL) {
		trigger_error('fromContent is deprecated, please use saveContent');

		return $this->saveContent($content, $name, $namespace, FALSE);
	}

	/**
	 * @deprecated
	 */
	public function fromUpload(Nette\Http\FileUpload $upload, $namespace = NULL) {
		trigger_error('fromUpload is deprecated, please use saveUpload.');
		$this->saveUpload($upload, $namespace, FALSE);
	}
}
