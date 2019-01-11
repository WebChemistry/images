<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\DeprecatedException;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resources\Providers\IImageProvider;
use WebChemistry\Images\Resources\Providers\ImageProvider;

class StringResource extends TransferResource {

	/** @var string */
	private $content;

	public function __construct(string $content, string $id) {
		$this->content = $content;
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
		return ImageProvider::createFromString($this->content);
	}

	/**
	 * @deprecated use getProvider() instead
	 * @return string
	 */
	public function getLocation(): string {
		throw new DeprecatedException('Use getProvider() instead.');
	}

}
