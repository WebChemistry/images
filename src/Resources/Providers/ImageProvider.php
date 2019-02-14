<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Providers;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;

final class ImageProvider implements IImageProvider {

	/** @var string|null */
	private $content;

	/** @var string|null */
	private $location;

	/** @var int|null */
	private $format;

	protected function __construct() {}

	public function toImage(IImageFactory $factory): Image {
		if ($this->location !== null) {
			return $factory->createFromFile($this->location, $this->format);
		}

		return $factory->createFromString($this->content, $this->format);
	}

	public static function createFromString(string $content, ?int &$format = null): self {
		$self = new self();
		$self->content = $content;
		$self->format = $format;

		return $self;
	}

	public static function createFromLocation(string $location, ?int &$format = null): self {
		$self = new self();
		$self->location = $location;
		$self->format = $format;

		return $self;
	}

}
