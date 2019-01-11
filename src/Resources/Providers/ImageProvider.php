<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Providers;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;

final class ImageProvider implements IImageProvider {

	/** @var string|null */
	private $content;

	/** @var string|null */
	private $location;

	protected function __construct() {}

	public function toImage(IImageFactory $factory): Image {
		if ($this->location !== null) {
			return $factory->createFromFile($this->location);
		}

		return $factory->createFromString($this->content);
	}

	public static function createFromString(string $content): self {
		$self = new self();
		$self->content = $content;

		return $self;
	}

	public static function createFromLocation(string $location): self {
		$self = new self();
		$self->location = $location;

		return $self;
	}

}
