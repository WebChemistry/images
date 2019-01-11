<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Providers;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;

interface IImageProvider {

	public function toImage(IImageFactory $factory): Image;

}
