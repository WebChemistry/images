<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use InvalidArgumentException;
use Nette\Http\FileUpload;
use WebChemistry\Images\MimeType\MimeType;
use WebChemistry\Images\Image\Providers\IImageProvider;
use WebChemistry\Images\Image\Providers\ImageProvider;

class UploadResource extends TransferResource {

	/** @var FileUpload */
	private $upload;

	public function __construct(FileUpload $upload) {
		if (!$upload->isOk() || !$upload->isImage()) {
			throw new InvalidArgumentException('Uploaded image is an image');
		}
		$this->upload = $upload;
		$this->setName($upload->getSanitizedName());
	}

	public function isFile(): bool {
		return true;
	}

	public function getFile(): string {
		return $this->upload->getTemporaryFile();
	}

	public function getContents(): string {
		return $this->upload->getContents();
	}

	public function getMimeType(): MimeType {
		return new MimeType((string) $this->upload->getContentType());
	}

	public function getUpload(): FileUpload {
		return $this->upload;
	}

	public function getProvider(): IImageProvider {
		return ImageProvider::createFromLocation($this->upload->getTemporaryFile());
	}

}
