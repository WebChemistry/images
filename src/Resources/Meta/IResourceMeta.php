<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Meta;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;

interface IResourceMeta {

	public function getNamespaceFolder(): ?string;

	public function getHashFolder(): ?string;

	public function getResource(): IResource;

	public function modify(Image $image);

	public function getSignature(): array;

	public function hasModifiers(): bool;

}
