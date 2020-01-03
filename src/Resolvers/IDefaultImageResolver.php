<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;

interface IDefaultImageResolver {

	public function resolve(?IResource $resource, ?string $default = null): ?IFileResource;

}