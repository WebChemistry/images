<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use InvalidArgumentException;
use WebChemistry\Images\MimeType\MimeType;
use WebChemistry\Images\Image\Providers\IImageProvider;
use WebChemistry\Images\Image\Providers\ImageProvider;

class StringResource extends TransferResource {

	/** @var string */
	private $content;

	/** @var MimeType */
	private $mimeType;

	public function __construct(string $content, string $id) {
		$this->content = $content;
		$this->setId($id);

		$this->mimeType = MimeType::fromString($content);
		if (!$this->mimeType->isImage()) {
			throw new InvalidArgumentException(sprintf('Given string is not an image, mime type %s given', $this->mimeType->toString()));
		}
	}

	public function getContents(): string {
		return $this->content;
	}

	public function getMimeType(): MimeType {
		return $this->mimeType;
	}

	public function getProvider(): IImageProvider {
		return ImageProvider::createFromString($this->content);
	}

}
