<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Content extends Creator {

	/** @var string */
	protected $content;

	public function __construct(WebChemistry\Images\Connectors\IConnector $connector, $content) {
		parent::__construct($connector);

		if (!is_string($content)) {
			throw new WebChemistry\Images\ImageStorageException('Content of image must be string.');
		}

		$this->content = $content;
	}

	/**
	 * @return int
	 */
	protected function getImageType() {
		$fileInfo = finfo_open();

		return $this->mimeToInteger(finfo_buffer($fileInfo, $this->content, FILEINFO_MIME_TYPE));
	}

	/**
	 * @return Info
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function save() {
		if (!$this->getName()) {
			throw new WebChemistry\Images\ImageStorageException('Image name must be set.');
		}

		$info = $this->getUniqueImageName();

		/** @var WebChemistry\Images\Bridges\Nette\Image $image */
		$image = WebChemistry\Images\Bridges\Nette\Image::fromString($this->content);
		$image->setQuality($this->quality);

		if ($this->getWidth() || $this->getHeight()) {
			$image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
		}

		$this->wakeUpCallbacks($image);

		$this->connector->save($image, $info, $this->getImageType());

		return $info;
	}
}
