<?php declare(strict_types = 1);

namespace WebChemistry\Images\Utils;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\StringResource;

class FixOrientation {

	public function fix(ITransferResource $resource, Image $image): void {
		if (!function_exists('exif_read_data')) {
			return;
		}

		if ($resource->getMimeType()->toString() !== 'image/jpeg') {
			return;
		}

		if ($resource->isFile()) {
			$exif = @exif_read_data($resource->getFile());
		} else if ($resource instanceof StringResource) {
			$exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($resource->getContents()));
		} else {
			return;
		}

		if (!$exif || !isset($exif['Orientation']) || !$exif['Orientation']) {
			return;
		}

		switch ($exif['Orientation']) {
			case 3:
			case 4:
				$image->rotate(180, 0);
				break;
			case 5:
			case 6:
				$image->rotate(-90, 0);
				break;
			case 7:
			case 8:
				$image->rotate(90, 0);
				break;
		}

		switch ($exif['Orientation']) {
			case 2:
			case 4:
			case 5:
			case 7:
				$image->flip(IMG_FLIP_HORIZONTAL);
		}
	}

}
