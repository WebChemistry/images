<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\Meta\IResourceMeta;

interface IHashResolver {

	public function getOriginal(IResourceMeta $meta): ?string;

	public function resolve(IResourceMeta $meta): ?string;

}
