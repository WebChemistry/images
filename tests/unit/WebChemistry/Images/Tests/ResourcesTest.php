<?php

namespace WebChemistry\Images\Tests;

use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\Resource;
use WebChemistry\Images\Resources\ResourceException;
use WebChemistry\Images\TypeException;
use WebChemistry\Test\TMethods;

class ResourcesTest extends \Codeception\Test\Unit {

	use TMethods;

	protected function _before() {
	}

	protected function _after() {
	}

	public function testName() {
		$resource = new ResourceMock('image.gif');
		$this->assertSame('image.gif', $resource->getName());

		$this->assertThrowException(function () {
			new ResourceMock(NULL);
		}, TypeException::class);
		$this->assertThrowException(function () {
			new ResourceMock('');
		}, TypeException::class);
		$this->assertThrowException(function () {
			new ResourceMock(5);
		}, TypeException::class);
	}

	public function testNamespace() {
		$resource = new ResourceMock('image.gif', 'namespace');
		$this->assertSame('namespace', $resource->getNamespace());

		$resource = new ResourceMock('image.gif', 'namespace/namespace');
		$this->assertSame('namespace/namespace', $resource->getNamespace());

		$this->assertThrowException(function () {
			new ResourceMock('image.gif', 5);
		}, TypeException::class);
		$this->assertThrowException(function () {
			new ResourceMock('image.gif', 'namespace@');
		}, ResourceException::class);
	}

	public function testId() {
		$resource = new ResourceIdMock('namespace/image.gif');

		$this->assertSame('image.gif', $resource->getName());
		$this->assertSame('namespace', $resource->getNamespace());

		$resource = new ResourceIdMock('image.gif');
		$this->assertSame('image.gif', $resource->getName());
		$this->assertNull($resource->getNamespace());

		$this->assertThrowException(function () {
			new ResourceIdMock(NULL);
		}, ResourceException::class);

		$this->assertThrowException(function () {
			new ResourceIdMock(10);
		}, ResourceException::class);
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
