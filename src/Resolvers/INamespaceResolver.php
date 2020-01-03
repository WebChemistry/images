<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\IResource;

interface INamespaceResolver {

	public function resolve(IResource $resource): ?string;

}
