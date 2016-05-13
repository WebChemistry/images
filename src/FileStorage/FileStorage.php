<?php

namespace WebChemistry\Images\FileStorage;

use Nette\Http\Request;
use WebChemistry\Images\AbstractStorage;
use WebChemistry\Images\FileStorage\Image\Image;

class FileStorage extends AbstractStorage {

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUri;

	/**
	 * @param string $defaultImage
	 * @param array $settings
	 * @param Request $request
	 */
	public function __construct($defaultImage, array $settings, Request $request) {
		parent::__construct($defaultImage, $settings);
		$this->basePath = $request->getUrl()->getBasePath();
		$this->baseUri = $request->getUrl()->getBaseUrl();
	}

	/**
	 * @return Image
	 */
	public function createImage() {
		$image = new Image($this->settings['wwwDir'], $this->settings['assetsDir']);
		foreach ($this->helpers as $name => $helper) {
			$image->addHelper($helper, $name);
		}
		$image->setQuality($this->settings['quality']);
		$image->setBasePath($this->basePath);
		$image->setBaseUri($this->baseUri);
		$image->setDefaultImage($this->defaultImage);

		return $image;
	}

}