<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\Meta\IResourceMeta;

interface INamespaceResolver {

	public function resolve(IResourceMeta $resource): ?string;

}
