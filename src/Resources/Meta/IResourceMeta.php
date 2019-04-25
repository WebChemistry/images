<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Meta;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;

interface IResourceMeta {

	public function getNamespaceFolder(): ?string;

	public function getOriginalHashFolder(): ?string;

	public function getHashFolder(): ?string;

	public function getResource(): IResource;

	public function modify(Image $image, ?string $path = null);

	public function toModify(): bool;

	public function getSignature(): array;

	public function hasModifiers(): bool;

}
