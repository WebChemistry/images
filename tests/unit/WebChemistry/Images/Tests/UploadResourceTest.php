<?php
namespace WebChemistry\Images\Tests;

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use WebChemistry\Images\Resources\ResourceException;
use WebChemistry\Images\Resources\Transfer\UploadResource;
use WebChemistry\Testing\TUnitTest;

class UploadResourceTest extends \Codeception\Test\Unit {

	use TUnitTest;

	protected function _before() {
	}

	protected function _after() {
	}

	public function testUpload() {
		$upload = new FileUpload([
			'name' => 'image.gif',
			'type' => 'image/gif',
			'size' => 1,
			'tmp_name' => IMAGE_GIF,
			'error' => 0,
		]);

		$resource = new UploadResource($upload);

		$this->assertNull($resource->getNamespace());
		$this->assertSame('image.gif', $resource->getName());
		$this->assertInstanceOf(Image::class, $resource->toImage());
	}

	public function testInvalidUpload() {
		$this->assertThrownException(function () {
			$upload = new FileUpload([
				'name' => 'image.gif',
				'type' => 'image/gif',
				'size' => 1,
				'tmp_name' => '',
				'error' => 4,
			]);
			new UploadResource($upload);
		}, ResourceException::class);
		$this->assertThrownException(function () {
			$upload = new FileUpload([
				'name' => 'text',
				'type' => 'text',
				'size' => 1,
				'tmp_name' => __FILE__,
				'error' => 4,
			]);
			new UploadResource($upload);
		}, ResourceException::class);
	}

}
