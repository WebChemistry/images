<?php

namespace WebChemistry\Images\Template;


use Nette\Http\IRequest;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;

class ImageModifiers implements IImageModifiers {

	/** @var string */
	private $baseUri;

	/** @var IImageStorage */
	private $imageStorage;

	public function __construct(IRequest $request, IImageStorage $imageStorage) {
		$this->baseUri = rtrim($request->getUrl()->getBaseUrl(), '/');
		$this->imageStorage = $imageStorage;
	}

	public function baseUri(IFileResource $resource) {
		$resource->setBaseUri();

		return $resource;
	}

	public function noImage(IFileResource $resource, $imageId) {
		$resource->setDefaultImage($imageId);

		return $resource;
	}


}