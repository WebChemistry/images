<?php

namespace WebChemistry\Images\Tests;

use Nette\Http\FileUpload;
use Test\ObjectHelper;
use Test\TemplateMock;
use WebChemistry\Images\Filters\BaseModifiers;
use WebChemistry\Images\Filters\FilterArgs;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Storage;
use WebChemistry\Images\Storages\LocalStorage;
use WebChemistry\Images\Storages\LocalStorageFilterLoader;
use WebChemistry\Testing\TUnitTest;

class TemplateTest extends \Codeception\Test\Unit {

	use TUnitTest;

	/** @var TemplateMock */
	private $latte;

	/** @var Storage */
	private $storage;

	protected function _before() {
		@mkdir(__DIR__ . '/output');

		$filterRegistry = ObjectHelper::createFilterRegistry();
		$filterRegistry->addFilter('resize', function (FilterArgs $args, int $width = 15, int $height = 15): void {});
		$filterRegistry->addFilter('args',
			function (FilterArgs $args, bool $b, bool $b1, string $s, string $s1, string $s2, int $i, float $f): void {}
		);

		$this->storage = $storage = ObjectHelper::createStorage(__DIR__, 'output', $filterRegistry, [
			'*' => 'default/upload.gif',
		]);

		$this->latte = ObjectHelper::createLatte($storage);
	}

	private function createUploadResource() {
		if (!file_exists(UPLOAD_GIF)) {
			copy(IMAGE_GIF, UPLOAD_GIF);
		}
		$upload = new FileUpload([
			'name' => 'upload.gif',
			'tmp_name' => UPLOAD_GIF,
			'type' => 'image/gif',
			'error' => 0,
			'size' => 1,
		]);

		return $this->storage->createUploadResource($upload);
	}

	protected function _after() {
		$this->services->fileSystem->removeDirRecursive(__DIR__ . '/output');
	}

	public function testBasicNoImage() {
		$string = $this->latte->renderToString($this->getFile('basic'));

		$this->assertSame('', trim($string));
	}

	public function testBasic() {
		$this->storage->save($this->createUploadResource());
		$string = $this->latte->renderToString($this->getFile('basic'));

		$this->assertSame('/output/original/upload.gif', trim($string));
	}

	public function testAttr() {
		$this->storage->save($this->createUploadResource());
		$string = $this->latte->renderToString($this->getFile('attr'));

		$this->assertSame('<img src="/output/original/upload.gif">', trim($string));
	}

	public function testNull() {
		$string = $this->latte->renderToString($this->getFile('null'));

		$this->assertSame('<img src="">', trim($string));
	}

	public function testEmpty() {
		$string = $this->latte->renderToString($this->getFile('empty'));

		$this->assertSame('', trim($string));
	}

	public function testNullDefaultImage() {
		$resource = $this->createUploadResource();
		$resource->setId('default/upload.gif');
		$this->storage->save($resource);

		$string = $this->latte->renderToString($this->getFile('null'));

		$this->assertSame('<img src="/output/default/original/upload.gif">', trim($string));
	}

	public function testNullDefaultImageResize() {
		$resource = $this->createUploadResource();
		$resource->setId('default/upload.gif');
		$this->storage->save($resource);

		$string = $this->latte->renderToString($this->getFile('null-resize'));

		$this->assertSame('<img src="/output/default/resize/upload.gif">', trim($string));
	}

	public function testImageResizeWithArgs() {
		$this->storage->save($this->createUploadResource());

		$string = $this->latte->renderToString($this->getFile('args'));

		$this->assertSame('/output/args_1_0_string_18.1_15_15_15.1/upload.gif', trim($string));
	}

	private function getFile($name) {
		return __DIR__ . '/data/template/' . $name . '.latte';
	}

}
