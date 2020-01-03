<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\IResource;

class NamespaceResolver implements INamespaceResolver {

	public function resolve(IResource $resource): ?string {
		return $resource->getNamespace();
	}

}
