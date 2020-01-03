<?php declare(strict_types = 1);

namespace WebChemistry\Images\Utils;

use WebChemistry\Images\Resources\IFileResource;

interface ISafeLink {

	public function call(?IFileResource $resource, array $options): ?string;

}