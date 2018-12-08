<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\Meta\IResourceMeta;

class NamespaceResolver implements INamespaceResolver {

	public function resolve(IResourceMeta $resource): ?string {
		return $resource->getResource()->getNamespace();
	}

}
