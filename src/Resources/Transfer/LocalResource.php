<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use InvalidArgumentException;
use WebChemistry\Images\MimeType\MimeType;
use WebChemistry\Images\Image\Providers\IImageProvider;
use WebChemistry\Images\Image\Providers\ImageProvider;

class LocalResource extends TransferResource {

	/** @var string */
	private $file;

	/** @var MimeType */
	private $mimeType;

	public function __construct(string $file, string $id) {
		if (!file_exists($file)) {
			throw new InvalidArgumentException(sprintf('File %s not exists', $file));
		}

		$this->file = $file;
		$this->setId($id);

		$this->mimeType = MimeType::fromFile($file);
		if (!$this->mimeType->isImage()) {
			throw new InvalidArgumentException(sprintf('File %s is not an image', $file));
		}
	}

	public function isFile(): bool {
		return true;
	}

	public function getFile(): string {
		return $this->file;
	}

	public function getContents(): string {
		return file_get_contents($this->file);
	}

	public function getMimeType(): MimeType {
		return $this->mimeType;
	}

	public function getProvider(): IImageProvider {
		return ImageProvider::createFromLocation($this->file);
	}

}
