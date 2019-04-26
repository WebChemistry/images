<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\DeprecatedException;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resources\Providers\IImageProvider;
use WebChemistry\Images\Resources\Providers\ImageProvider;

class StringResource extends TransferResource {

	/** @var string */
	private $content;

	/** @var int|null */
	private $format;

	public function __construct(string $content, string $id, ?int &$format = null) {
		$this->content = $content;
		$this->format = $format;
		$this->setId($id);
	}

	/**
	 * @deprecated use getProvider() instead
	 * @param IImageFactory|null $factory
	 * @return \Nette\Utils\Image|void
	 */
	public function toImage(IImageFactory $factory = null) {
		throw new DeprecatedException('Use getLocation() instead.');
	}

	public function getProvider(): IImageProvider {
		return ImageProvider::createFromString($this->content, $this->format);
	}

	/**
	 * @return null
	 */
	public function getLocation(): ?string {
		return null;
	}

}
