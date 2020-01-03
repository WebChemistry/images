<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Meta\IResourceMeta;

interface IHashResolver {

	public function getOriginal(IResource $resource): ?string;

	public function resolve(IResource $resource): ?string;

}
