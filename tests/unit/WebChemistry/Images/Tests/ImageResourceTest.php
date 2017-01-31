<?php
namespace WebChemistry\Images\Tests;

use Nette\Utils\Image;
use WebChemistry\Images\Resources\Transfer\LocalResource;

class ImageResourceTest extends \Codeception\Test\Unit {

	protected function _before() {

	}

	protected function _after() {

	}

	// tests
	public function testResource() {
		$resource = new LocalResource(IMAGE_GIF, 'namespace/image.gif');

		$this->assertInstanceOf(Image::class, $resource->toImage());
		$this->assertSame('image.gif', $resource->getName());
		$this->assertSame('namespace', $resource->getNamespace());

		$resource->setNamespace('name');
		$this->assertSame('name', $resource->getNamespace());

		$resource->setName('name.gif');
		$this->assertSame('name.gif', $resource->getName());
	}
}
