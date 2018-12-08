<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Meta;

use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Modifiers\IModifiers;
use WebChemistry\Images\Resolvers\IHashResolver;
use WebChemistry\Images\Resolvers\INamespaceResolver;
use WebChemistry\Images\Resources\IResource;

class ResourceMetaFactory implements IResourceMetaFactory {

	/** @var IModifiers */
	private $modifiers;

	/** @var IImageFactory */
	private $imageFactory;

	/** @var IHashResolver */
	private $hashResolver;

	/** @var INamespaceResolver */
	private $namespaceResolver;

	public function __construct(IModifiers $modifiers, IImageFactory $imageFactory, IHashResolver $hashResolver,
								INamespaceResolver $namespaceResolver) {
		$this->modifiers = $modifiers;
		$this->imageFactory = $imageFactory;
		$this->hashResolver = $hashResolver;
		$this->namespaceResolver = $namespaceResolver;
	}

	public function create(IResource $resource): IResourceMeta {
		if (!$resource->__meta) {
			$resource->__meta = new ResourceMeta(
				$resource, $this->modifiers, $this->hashResolver, $this->namespaceResolver
			);
		}

		return $resource->__meta;
	}

}
