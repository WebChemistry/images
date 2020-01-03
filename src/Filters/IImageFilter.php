<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\Resources\IResource;

interface IImageFilter {

	public function filter(IResource $resource, ?IImage $image = null);

}