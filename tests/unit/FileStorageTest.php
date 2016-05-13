<?php

class FileStorageTest extends \Codeception\TestCase\Test {

	/** @var \WebChemistry\Images\FileStorage\FileStorage */
	private $storage;

	protected function _before() {
		$this->storage = new \WebChemistry\Images\FileStorage\FileStorage('image.gif', [
			'helpers' => [
				'sharpen' => new \WebChemistry\Images\Helpers\Sharpen()
			],
			'wwwDir' => __DIR__ . '/../_data',
			'assetsDir' => 'tmp',
			'defaultImage' => 'image.gif'
		] + (new \WebChemistry\Images\DI\Extension)->defaults, new \Nette\Http\Request(new \Nette\Http\UrlScript()));
		@copy(__DIR__ . '/../_data/image.gif', __DIR__ . '/../_data/assets/original/image.gif'); // move in FileUpload
	}

	protected function _after() {
		foreach (\Nette\Utils\Finder::findFiles('*')->from(__DIR__ . '/../_data/tmp') as $file) {
			@unlink((string) $file);
		}
		@copy(__DIR__ . '/../_data/image.gif', __DIR__ . '/../_data/assets/original/image.gif'); // move in FileUpload
	}

	private function createUpload() {
		return new \Nette\Http\FileUpload([
			'tmp_name' => __DIR__ . '/../_data/assets/original/image.gif',
			'type' => 'image/gif',
			'size' => 0,
			'error' => 0,
			'name' => 'image.gif'
		]);
	}

	public function testDefaultImage() {
		$this->assertSame('image.gif', $this->storage->createImage()->getDefaultImage());

		$upload = $this->createUpload();
		$absoluteName = $this->storage->saveUpload($upload);

		$this->assertSame('tmp/original/image.gif', $this->storage->get('noimage.gif')->getLink());
		$this->assertSame(\WebChemistry\Images\Image\PropertyAccess::NO_IMAGE,
			$this->storage->get('noimage.gif', NULL, NULL, 'noimage.png')->getLink());
	}

	public function testSaveUpload() {
		$upload = $this->createUpload();
		$absoluteName = $this->storage->saveUpload($upload);
		$image = $this->storage->get($absoluteName);

		$this->assertSame('image.gif', $absoluteName);
		$this->assertTrue($image->isExists());

		// Deleting
		$this->assertTrue($image->delete());
		$this->assertFalse($image->isExists());
	}

	public function testSaveImage() {
		$netteImage = $this->createUpload()->toImage();
		$absoluteName = $this->storage->saveImage($netteImage, 'file.gif');
		$image = $this->storage->get($absoluteName);

		$this->assertSame('file.gif', $absoluteName);
		$this->assertTrue($image->isExists());

		// Deleting
		$this->assertTrue($image->delete());
		$this->assertFalse($image->isExists());
	}

	public function testResize() {
		$upload = $this->createUpload();
		$absoluteName = $this->storage->saveUpload($upload);
		$image = $this->storage->get($absoluteName);

		$this->assertSame('tmp/20%25x150/image.gif', $this->storage->get($absoluteName, '20%x150')->getLink());

		// Deleting
		$image = $this->storage->get($absoluteName, '20%x150');

		$this->assertTrue($image->delete());
		$this->assertFalse($image->isExists());
		$this->assertFalse($image->getOriginalClass()->isExists());
	}

	public function testFull() {
		$upload = $this->createUpload();
		$absoluteName = $this->storage->saveUpload($upload);
		$image = $this->storage->get($absoluteName);

		$this->assertSame('tmp/20%25x150_8-8191d4d245b5af1a1e4b60a75ac32f4c/image.gif',
			$this->storage->get($absoluteName, '20%x150|sharpen', 'exact')->getLink());

		$this->assertTrue($image->isExists());

		// Deleting
		$this->assertTrue($image->delete());
		$this->assertFalse($image->isExists());
	}

	public function testNamespaceFull() {
		$upload = $this->createUpload();
		$absoluteName = $this->storage->saveUpload($upload, 'namespace/namespace');
		$image = $this->storage->get($absoluteName);

		$this->assertSame('tmp/namespace/namespace/20%25x150_8-8191d4d245b5af1a1e4b60a75ac32f4c/image.gif',
			$this->storage->get($absoluteName, '20%x150|sharpen', 'exact')->getLink());

		$this->assertTrue($image->isExists());

		// Deleting
		$this->assertTrue($image->delete());
		$this->assertFalse($image->isExists());
	}

	public function testQuality() {
		$image = $this->storage->createImage();
		$this->assertSame(85, $image->getQuality());
	}

}