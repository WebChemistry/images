<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use WebChemistry\Images\MimeType\MimeType;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Image\Providers\IImageProvider;

interface ITransferResource extends IResource {

	public function getMimeType(): MimeType;

	public function setName(string $name);

	public function isFile(): bool;

	public function getFile(): string;

	public function getContents(): string;

	/**
	 * @internal
	 */
	public function setSaved();

	public function getProvider(): IImageProvider;

}
