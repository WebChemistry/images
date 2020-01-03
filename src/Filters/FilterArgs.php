<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

use LogicException;
use Nette\Utils\Image;
use WebChemistry\Images\Image\IImage;

final class FilterArgs {

	/** @var Image|IImage|null */
	protected $image;

	/**
	 * @param Image|IImage|null $image
	 */
	public function __construct(?Image $image) {
		$this->image = $image;
	}

	public function hasImage(): bool {
		return (bool) $this->image;
	}

	/**
	 * @return Image|IImage|null
	 */
	public function tryGetImage(): ?Image {
		return $this->image;
	}

	/**
	 * @return Image|IImage
	 */
	public function getImage(): Image {
		if (!$this->image) {
			throw new LogicException('Image is not set');
		}

		return $this->image;
	}

}
