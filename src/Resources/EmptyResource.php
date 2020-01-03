<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

final class EmptyResource extends Resource implements IFileResource {

	public function getOriginal(): IFileResource {
		return new self();
	}

}
