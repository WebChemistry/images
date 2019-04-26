<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resources\Providers\IImageProvider;
use WebChemistry\Images\Resources\Providers\ImageProvider;
use WebChemistry\Images\Resources\ResourceException;

class UploadResource extends TransferResource {

	/** @var FileUpload */
	private $upload;

	/**
	 * @param FileUpload $upload
	 * @throws ResourceException
	 */
	public function __construct(FileUpload $upload) {
		if (!$upload->isOk() || !$upload->isImage()) {
			throw new ResourceException('Uploaded image is not ok.');
		}
		$this->upload = $upload;
		$this->setName($upload->getSanitizedName());
	}

	/**
	 * @return FileUpload
	 */
	public function getUpload(): FileUpload {
		return $this->upload;
	}

	/**
	 * @deprecated use getProvider() instead
	 * @param IImageFactory $factory
	 * @return Image
	 * @throws \Nette\Utils\ImageException
	 */
	public function toImage(?IImageFactory $factory = null) {
		if ($factory) {
			return $factory->createFromFile($this->upload->getTemporaryFile());
		}

		return $this->upload->toImage();
	}

	/**
	 * @return string
	 */
	public function getLocation(): string {
		return $this->upload->getTemporaryFile();
	}

	public function getProvider(): IImageProvider {
		return ImageProvider::createFromLocation($this->upload->getTemporaryFile());
	}

}
