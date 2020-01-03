<?php declare(strict_types = 1);

namespace Test;

use InvalidArgumentException;
use WebChemistry\Images\MimeType\MimeType;
use WebChemistry\Images\Resources\Transfer\StringResource;

final class InvalidTransferResource extends StringResource {

	public function __construct(string $content, string $id) {

	}

	public function getMimeType(): MimeType {
		return new MimeType('image/jpeg');
	}

	public function getSource(): string {
		throw new InvalidArgumentException('foo');

		return parent::getSource();
	}

}
