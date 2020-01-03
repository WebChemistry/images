<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use WebChemistry\Images\Resources\Transfer\ITransferResource;

interface IImageSuffixResolver {

	public function resolve(ITransferResource $resource): void;

}