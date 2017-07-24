<?php
namespace WebChemistry\Images\Storages\S3;


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use WebChemistry\Images\ImageStorageException;
use WebChemistry\Images\Modifiers\ModifierContainer;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\IFileResource;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

class S3Facade{

	/** @var array */
	private $config;

	/** @var bucket */
	private $bucket;

	/** @var ModifierContainer */
	private $modifiers;

	/** @var S3Client */
	private $client;

	/**
	 * S3Facade constructor.
	 */
	public function __construct(array $config, ModifierContainer $modifierContainer){
		$this->config = $config;
		$this->modifiers = $modifierContainer;
		$this->bucket = $config['bucket'];
		unset($config['bucket']);

		$this->client = new S3Client($config);
	}

	public function save(ITransferResource $resource){
		try{
			$result = $this->client->putObject([
				'Bucket' => $this->bucket,
				'Key' => $resource->getId(),
				'SourceFile' => $resource->getLocation(),
				'ContentType' => 'image/'.array_reverse(explode('.', $resource->getName()))[0],
				'ACL' => 'public-read'

			]);
		}catch(S3Exception $e){
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
		return new FileResource($resource->getId());
	}

	public function link(IFileResource $resource){
		try{
			$result = $this->client->getObjectUrl($this->bucket, $resource->getId());
		}catch(S3Exception $e){
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
		return $result;
	}

	public function move(IFileResource $src, IFileResource $dest){
		$this->copy($src, $dest);
		$this->delete($src);
	}

	public function copy(IFileResource $src, IFileResource $dest){
		try{
			$result = $this->client->copyObject([
				'Bucket' => $this->bucket,
				'Key' => $dest->getId(),
				'CopySource' => "{$this->bucket}/{$src->getId()}"
			]);
		}catch(S3Exception $e){
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}

	public function delete(IFileResource $resource){
		try{
			$result = $this->client->deleteObject([
				'Bucket' => $this->bucket,
				'Key' => $resource->getId()
			]);
		}catch(S3Exception $e){
			throw new ImageStorageException($e->getMessage(), $e->getCode(), $e);
		}
	}
}