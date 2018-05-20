<?php

namespace WebChemistry\Images\Modifiers;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\IResource;

interface IModifiers {

	/**
	 * @param IResource $resource
	 * @return array
	 */
	public function modifiersFromResource(IResource $resource);

	/**
	 * @param IResource $resource
	 * @return array
	 */
	public function extractActiveAliases(IResource $resource);

	/**
	 * @param IResource $resource
	 * @return ImageParameters
	 */
	public function getImageParameters(IResource $resource);

	/**
	 * @param IResource $resource
	 * @param Image $image
	 * @return void
	 */
	public function modifyImage(IResource $resource, Image $image);

}
