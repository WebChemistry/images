<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources;

interface IFileResource extends IResource {

	public function getOriginal(): IFileResource;

}
