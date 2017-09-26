<?php

namespace WebChemistry\Images\Storages\S3;


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use WebChemistry\Images\Image\IImageFactory;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\IResource;
use WebChemistry\Images\Resources\Transfer\ImageObjectResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;

class S3Facade {

	const ORIGINAL = 'original';

	/** @var array */
	private $config;

	/** @var string */
	private $bucket;

	/** @var \WebChemistry\Images\Modifiers\ModifierContainer  */
	private $modifierContainer;

	/** @var \WebChemistry\Images\Image\IImageFactory  */
	private $imageFactory;

	/** @var \Aws\S3\S3Client  */
	private $client;

	/** @var bool  */
	private $backCompatibility = FALSE;

	/**
	 * @param array                                             $config
	 * @param \WebChemistry\Images\Modifiers\ModifierContainer  $modifierContainer
	 * @param \WebChemistry\Images\Image\IImageFactory          $imageFactory
	 */
	public function __construct(array $config, ModifierContainer $modifierContainer, IImageFactory $imageFactory) {
		$this->config = $config;
		$this->modifierContainer = $modifierContainer;
		$this->imageFactory = $imageFactory;
		$this->bucket = $config['bucket'];
		unset($config['bucket']);

		$this->client = new S3Client($config);
	}

	/**
	 * @param bool $backCompatibility
	 *
	 * @return void
	 */
	public function setBackCompatibility($backCompatibility) {
		$this->backCompatibility = (bool) $backCompatibility;
	}

	/**
	 * @param \WebChemistry\Images\Resources\Transfer\ITransferResource $resource
	 * @param bool                                                      $forceModify
	 *
	 * @return \WebChemistry\Images\Resources\FileResource
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	public function save(ITransferResource $resource, $forceModify = FALSE) {
		try {
			$this->client->putObject([
				'Bucket' => $this->bucket,
				'Key' => $this->getResourceId($resource, $forceModify),
				'Body' => file_get_contents($resource->getLocation()),
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
	 * @return string|NULL
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	public function link(IFileResource $resource) {
		if (is_string($link = $this->getLink($resource))) {
			return $link;
		} elseif (!$resource->toModify() || !is_string($originalLink = $this->getLink($resource->getOriginal()))) {
			return FALSE;
		}

		$image = $this->imageFactory->createFromFile($originalLink);
		$this->modifierContainer->modifyImage($resource, $image);
		$tmpResource = new ImageObjectResource($image, $resource->getId());
		$tmpResource->setAliases($resource->getAliases());
		$this->save($tmpResource, TRUE);
		unset($tmpResource);

		return ($link = $this->getLink($resource)) ? $link : FALSE;
	}

	/**
	 * @param \WebChemistry\Images\Resources\IFileResource $src
	 * @param \WebChemistry\Images\Resources\IFileResource $dest
	 *
	 * @return void
	 */
	public function move(IFileResource $src, IFileResource $dest) {
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
	public function copy(IFileResource $src, IFileResource $dest) {
		try{
			$resource = new LocalResource($this->getResourceId($src->getOriginal()), $dest->getId());
			$resource->setAliases($dest->getAliases());
			$this->save($resource);
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
	public function delete(IFileResource $resource) {
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
	 * @return NULL|string
	 * @throws \WebChemistry\Images\ImageStorageException
	 */
	private function getLink(IFileResource $resource) {
		try {
			$getLink = function ($id) {
				return $this->client->doesObjectExist($this->bucket, $id)
					? $this->client->getObjectUrl($this->bucket, $id)
					: NULL;
			};

			$link = $getLink($this->getResourceId($resource));
			if (NULL === $link && $this->backCompatibility) {
				$link = $getLink($this->getResourceId($resource, FALSE, FALSE));
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
	private function getResourceRoot(IResource $resource) {
		$basePath = $resource->getNamespace();
		if ($basePath) {
			$basePath .= '/';
		}

		return $basePath;
	}

	/**
	 * @param \WebChemistry\Images\Resources\IResource  $resource
	 * @param bool                                      $forceModify
	 * @param bool                                      $includeOriginalNamespace
	 *
	 * @return string
	 */
	private function getResourceId(IResource $resource, $forceModify = FALSE, $includeOriginalNamespace = TRUE) {
		$basePath = $this->getResourceRoot($resource);
		if (!$forceModify && ($resource instanceof ITransferResource || !$resource->toModify())) {
			if (!$includeOriginalNamespace) {

				return $basePath . $resource->getName();
			}
			$namespace = self::ORIGINAL;
		} else {
			$namespace = implode('.', $resource->getAliases());
		}

		return $basePath . $namespace . '/' . $resource->getName();
	}
}
