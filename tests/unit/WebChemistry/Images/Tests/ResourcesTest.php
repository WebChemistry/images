<?php

namespace WebChemistry\Images\Tests;

use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\Resource;
use WebChemistry\Images\Resources\ResourceException;
use WebChemistry\Images\TypeException;
use WebChemistry\Testing\TUnitTest;

class ResourcesTest extends \Codeception\Test\Unit {

	use TUnitTest;

	protected function _before() {
	}

	protected function _after() {
	}

	public function testName() {
		$resource = new ResourceMock('image.gif');
		$this->assertSame('image.gif', $resource->getName());
	}

	public function testNamespace() {
		$resource = new ResourceMock('image.gif', 'namespace');
		$this->assertSame('namespace', $resource->getNamespace());

		$resource = new ResourceMock('image.gif', 'namespace/namespace');
		$this->assertSame('namespace/namespace', $resource->getNamespace());

		$resource = new ResourceMock('image.gif', 'namespace/name-space');
		$this->assertSame('namespace/name-space', $resource->getNamespace());
	}

	public function testId() {
		$resource = new ResourceIdMock('namespace/image.gif');

		$this->assertSame('image.gif', $resource->getName());
		$this->assertSame('namespace', $resource->getNamespace());

		$resource = new ResourceIdMock('image.gif');
		$this->assertSame('image.gif', $resource->getName());
		$this->assertNull($resource->getNamespace());
	}

	public function testPrefix() {
		$resource = new ResourceMock('image.gif');

		$this->assertNull($resource->getPrefix());
		$resource->generatePrefix();
		$this->assertSame(10, strlen($resource->getPrefix()));
	}

}

/////////////////////////////////////////////////////////////////

class ResourceMock extends Resource {

	public function __construct($name, $namespace = NULL) {
		$this->setName($name);
		$this->setNamespace($namespace);
	}

}

class ResourceIdMock extends Resource {

	public function __construct($id) {
		$this->parseId($id);
	}

}
