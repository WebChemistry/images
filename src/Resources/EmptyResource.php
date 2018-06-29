<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

class EmptyResource extends Resource implements IFileResource {

	public function getOriginal(): IFileResource {
		return new self();
	}

}
