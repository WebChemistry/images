<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Utils\Image;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Providers\IImageProvider;

interface ITransferResource extends IResource {

	/**
	 * @param string $name
	 */
	public function setName(string $name);

	/**
	 * @deprecated use getProvider() instead
	 * @param IImageFactory $factory
	 * @return Image
	 */
	public function toImage(IImageFactory $factory = null);

	/**
	 * @internal
	 */
	public function setSaved();

	/**
	 * @return string|null
	 */
	public function getLocation();

	/**
	 * @return IImageProvider
	 */
	public function getProvider(): IImageProvider;

}
