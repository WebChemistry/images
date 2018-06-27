<?php declare(strict_types = 1);

namespace WebChemistry\Images\Modifiers;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;

interface IModifiers {

	public function modifiersFromResource(IResource $resource): array;

	public function extractActiveAliases(IResource $resource): array;

	public function getImageParameters(IResource $resource): ImageParameters;

	public function modifyImage(IResource $resource, Image $image): void;

}
