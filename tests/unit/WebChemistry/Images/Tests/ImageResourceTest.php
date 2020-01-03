<?php
namespace WebChemistry\Images\Tests;

use WebChemistry\Images\Image\Providers\IImageProvider;
use WebChemistry\Images\Resources\Transfer\LocalResource;

class ImageResourceTest extends \Codeception\Test\Unit {


	// tests
	public function testResource() {
		$resource = new LocalResource(IMAGE_GIF, 'namespace/image.gif');

		$this->assertInstanceOf(IImageProvider::class, $resource->getProvider());
		$this->assertSame('image.gif', $resource->getName());
		$this->assertSame('namespace', $resource->getNamespace());

		$resource->setNamespace('name');
		$this->assertSame('name', $resource->getNamespace());

		$resource->setName('name.gif');
		$this->assertSame('name.gif', $resource->getName());
	}
}
