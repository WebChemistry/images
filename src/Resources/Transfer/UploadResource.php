<?php

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resources\ResourceException;

class UploadResource extends TransferResource {

	/** @var FileUpload */
	private $upload;

	/**
	 * @param FileUpload $upload
	 * @throws ResourceException
	 */
	public function __construct(FileUpload $upload) {
		if (!$upload->isOk() || (!$upload->isImage() && $upload->getContentType() !== 'image/svg+xml')) {
			throw new ResourceException('Uploaded image is not ok.');
		}
		$this->upload = $upload;
		$this->setName($upload->getSanitizedName());
	}

	/**
	 * @return FileUpload
	 */
	public function getUpload() {
		return $this->upload;
	}

	/**
	 * @param IImageFactory $factory
	 * @return Image
	 */
	public function toImage(IImageFactory $factory = NULL) {
		if ($factory) {
			return $factory->createFromFile($this->upload->getTemporaryFile());
		}

		return $this->upload->toImage();
	}

	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->upload->getTemporaryFile();
	}

}
