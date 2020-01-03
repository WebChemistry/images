<?php declare(strict_types = 1);

namespace WebChemistry\Images\MimeType;

use finfo;
use Nette\SmartObject;

final class MimeType {

	private const IMAGE_MIME_TYPES = ['image/gif' => true, 'image/png' => true, 'image/jpeg' => true, 'image/webp' => true];

	use SmartObject;

	/** @var string */
	private $mimeType;

	public function __construct(string $mimeType) {
		$this->mimeType = $mimeType;
	}

	public static function fromFile(string $file) {
		return new self((new finfo(FILEINFO_MIME_TYPE))->file($file));
	}

	public static function fromString(string $string) {
		return new self((new finfo(FILEINFO_MIME_TYPE))->buffer($string));
	}

	public function isImage(): bool {
		return isset(self::IMAGE_MIME_TYPES[$this->mimeType]);
	}

	public function toString(): string {
		return $this->mimeType;
	}

}
