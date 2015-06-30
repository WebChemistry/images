<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Upload extends Creator {

	/** @var Nette\Http\FileUpload */
	protected $fileUpload;

	/**
	 * @param WebChemistry\Images\Connectors\IConnector $connector
	 * @param Nette\Http\FileUpload                     $fileUpload
	 * @throws WebChemistry\Images\ImageStorageException
	 */
	public function __construct(WebChemistry\Images\Connectors\IConnector $connector, Nette\Http\FileUpload $fileUpload) {
		parent::__construct($connector);

		if (!$fileUpload->isOk()) {
			throw new WebChemistry\Images\ImageStorageException('Uploaded file is invalid.');
		}

		$this->fileUpload = $fileUpload;

		$this->setName($fileUpload->getSanitizedName());
	}

	/**
	 * @return Info
	 * @throws Nette\Utils\UnknownImageFileException
	 */
	public function save() {
		$info = $this->getUniqueImageName();

		/** @var WebChemistry\Images\Bridges\Nette\Image $image */
		$image = WebChemistry\Images\Bridges\Nette\Image::fromFile($this->fileUpload->getTemporaryFile());
		$image->setQuality($this->quality);

		if ($this->getWidth() || $this->getHeight()) {
			$image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
		}

		$this->wakeUpCallbacks($image);

		$this->connector->save($image, $info, $this->mimeToInteger($this->fileUpload->getContentType()));

		return $this->savedInfo = $info;
	}
}
