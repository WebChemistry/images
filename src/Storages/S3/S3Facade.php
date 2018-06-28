<?php declare(strict_types = 1);

namespace WebChemistry\Images\Storages\S3;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use WebChemistry\Images\Helpers;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\Image\Image;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ImageObjectResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

class S3Facade {

	/** @var array */
	private $config;

	/** @var string */
	private $bucket;

	/** @var \WebChemistry\Images\Modifiers\ModifierContainer */
	private $modifierContainer;

	/** @var \WebChemistry\Images\Image\IImageFactory */
	private $imageFactory;

	/** @var \Aws\S3\S3Client */
	private $client;

	/** @var bool */
	private $backCompatibility = false;

	public function __construct(array $config, ModifierContainer $modifierContainer, IImageFactory $imageFactory) {
		$this->config = $config;
		$this->modifierContainer = $modifierContainer;
		$this->imageFactory = $imageFactory;
		$this->bucket = $config['bucket'];
		unset($config['bucket']);

		$this->client = new S3Client($config);
	}

	public function setBackCompatibility(bool $backCompatibility): void {
		$this->backCompatibility = $backCompatibility;
	}

	/**
	 * @param ITransferResource $resource
	 * @param bool $forceModify
	 *
	 * @return \WebChemistry\Images\Resources\FileResource
	 * @throws ImageStorageException
	 * @throws \WebChemistry\Images\Modifiers\ModifierException
	 * @throws \WebChemistry\Images\Resources\ResourceException
	 */
	public function save(ITransferResource $resource, bool $forceModify = false): IFileResource {
		try {
			$image = $resource->toImage($this->imageFactory);
			$this->modifierContainer->modifyImage($resource, $image);

			$this->client->putObject([
				'Bucket' => $this->bucket,
				'Key' => $this->getResourceId($resource, $forceModify),
				'Body' => $image->toString(Image::getImageType($resource)),
				'ContentType' => 'image/' . array_reverse(explode('.', $resource->getName()))[0],
				'ACL' => 'public-read',
			]);
		} catch (S3Exception $e) {
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}

		return new FileResource($resource->getId());
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $resource
	 *
	 * @return string|null
	 * @throws ImageStorageException
	 * @throws \WebChemistry\Images\Modifiers\ModifierException
	 * @throws \WebChemistry\Images\Resources\ResourceException
	 */
	public function link(IFileResource $resource): ?string {
		if (is_string($link = $this->getLink($resource))) {
			return $link;
		}
		$originalLink = $this->getLink($resource->getOriginal());
		if (!$resource->toModify() || !is_string($originalLink)) {
			return null;
		}

		$image = $this->imageFactory->createFromFile($originalLink);
		$tmpResource = new ImageObjectResource($image, $resource->getId());
		$tmpResource->setAliases($resource->getAliases());
		$this->save($tmpResource, true);
		unset($tmpResource);

		return ($link = $this->getLink($resource)) ? $link : null;
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $src
	 * @param \WebChemistry\Images\Resources\IFileResource $dest
	 *
	 * @return void
	 * @throws ImageStorageException
	 */
	public function move(IFileResource $src, IFileResource $dest): void {
		$this->copy($src, $dest);
		$this->delete($src);
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $src
	 * @param \WebChemistry\Images\Resources\IFileResource $dest
	 *
	 * @return void
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	public function copy(IFileResource $src, IFileResource $dest): void {
		try{
			$this->client->copyObject([
				'Bucket' => $this->bucket,
				'Key' => $this->getResourceId($dest->getOriginal()),
				'CopySource' => "{$this->bucket}/{$this->getResourceId($src->getOriginal())}",
				'ACL' => 'public-read',
			]);
		}catch(S3Exception $e){
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $resource
	 *
	 * @return void
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	public function delete(IFileResource $resource): void {
		try{
			$this->client->deleteMatchingObjects(
				$this->bucket,
				$this->getResourceRoot($resource),
				'~' . preg_quote($resource->getName()) . '$~'
			);
		} catch (S3Exception $e) {
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $resource
	 *
	 * @return null|string
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	private function getLink(IFileResource $resource): ?string {
		try {
			$getLink = function ($id) {
				return $this->client->doesObjectExist($this->bucket, $id)
					? $this->client->getObjectUrl($this->bucket, $id)
					: null;
			};

			$link = $getLink($this->getResourceId($resource));
			if (null === $link && $this->backCompatibility) {
				$link = $getLink($this->getResourceId($resource, false, false));
			}

			return $link;
		} catch (S3Exception $e) {
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * @param \WebChemistry\Images\Resources\IResource $resource
	 *
	 * @return string
	 */
	private function getResourceRoot(IResource $resource): string {
		$basePath = $resource->getNamespace();
		if ($basePath) {
			$basePath .= '/';
		}

		return $basePath;
	}

	/**
	 * @param \WebChemistry\Images\Resources\IResource $resource
	 * @param bool $forceModify
	 * @param bool $includeOriginalNamespace
	 *
	 * @return string
	 */
	private function getResourceId(IResource $resource, bool $forceModify = false, bool $includeOriginalNamespace = true): string {
		$basePath = $this->getResourceRoot($resource);
		if (!$forceModify && ($resource instanceof ITransferResource || !$resource->toModify())) {
			if (!$includeOriginalNamespace) {

				return $basePath . $resource->getName();
			}
			$namespace = IResource::ORIGINAL;
		} else {
			$namespace = Helpers::getNameByAliases($resource->getAliases());
		}

		return $basePath . $namespace . '/' . $resource->getName();
	}

}
