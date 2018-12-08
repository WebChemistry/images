<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Meta;

use WebChemistry\Images\Resources\IResource;

interface IResourceMetaFactory {

	public function create(IResource $resource): IResourceMeta;

}
