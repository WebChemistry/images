<?php

use Environment as E;

class ImageTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

	/** @var \WebChemistry\Images\Storage */
	protected $storage;

    protected function _before()
    {
		$this->storage = Environment::getByType('WebChemistry\Images\Storage');
		E::copy('/test.png', [
			'original',
			'namespace/original',
			'namespace/namespace/original',
			'noimage.png' => 'noimage/original',
			'namespace/250x'
		], '/assets');
    }

	private function createUpload() {
		if (!file_exists(E::getDataDir('/test-upload.png'))) {
			copy(E::getWwwDir('/test.png'), E::getWwwDir('/test-upload.png'));
		}
		
		return new \Nette\Http\FileUpload([
			'name' => 'test-upload.png',
			'tmp_name' => E::getWwwDir('/test-upload.png'),
			'type' => 'image/jpeg',
			'size' => 6502,
			'error' => 0
		]);
	}

    protected function _after()
    {
		//Environment::cleanFrom('/assets');
    }

    // tests
    public function testImageClass()
    {
		$image = $this->storage->get('namespace/name.jpg', '140x150', 'fill');
		$this->assertInstanceOf('WebChemistry\Images\Image\Image', $image);
		$this->assertEquals(4, $image->getFlag());
		$this->assertEquals('namespace', $image->getNamespace());
		$this->assertEquals('name.jpg', $image->getName());
		$this->assertEquals(140, $image->getWidth());
		$this->assertEquals(150, $image->getHeight());


		$image = $this->storage->get('name.jpg', '100%x');
		$this->assertInstanceOf('WebChemistry\Images\Image\Image', $image);
		$this->assertEquals(NULL, $image->getNamespace());
		$this->assertEquals('100%', $image->getWidth());
		$this->assertEquals(NULL, $image->getHeight());
		$this->assertEquals(NULL, $image->getFlag());

		$image = $this->storage->get('name.jpg', 'x150');
		$this->assertEquals(150, $image->getHeight());
		$this->assertEquals(NULL, $image->getWidth());
    }

	public function testInfo() {
		$info = $this->storage->get('namespace/name.jpg', '140x150', 'fill')->getInfo();
		$this->assertInstanceOf('WebChemistry\Images\Image\Info', $info);
		$this->assertEquals(4, $info->getFlag());
		$this->assertEquals('namespace', $info->getNamespace());
		$this->assertEquals(NULL, $info->getPrefix());
		$this->assertEquals('name.jpg', $info->getName());
		$this->assertEquals(140, $info->getWidth());
		$this->assertEquals(150, $info->getHeight());
		$this->assertEquals('namespace/name.jpg', $info->getAbsoluteName());
		$this->assertEquals('namespace/name.jpg', (string) $info);
		$this->assertEquals('140x150_4', $info->getBaseFolder());
		$this->assertEquals('name.jpg', $info->getNameWithPrefix());

		$info = $this->storage->get('name.jpg', 'x150')->getInfo();
		$this->assertEquals('name.jpg', $info->getAbsoluteName());
		$this->assertEquals('name.jpg', (string) $info);

		$info = $this->storage->get('test.png')->getInfo();
		$this->assertInstanceOf('WebChemistry\Images\Bridges\Nette\Image', $info->getNetteImageClass());
		$this->assertSame(array(
			0 => 128, 1 => 128, 2 => 3, 3 => 'width="128" height="128"', 'bits' => 8, 'mime' => 'image/png'
		), $info->getImageSize());
		$this->assertSame(3, $info->getImageType());

	}

	public function testCreateImage() {
		$image = $this->storage->get('test.png', '120x150');
		$info = $image->getInfo();
		
		$this->assertEquals('assets/original/test.png', $image->getLink(TRUE));
		$this->assertFileExists(E::getWwwDir('/assets/original/test.png'));
		$this->assertEquals('assets/120x150/test.png', $image->getLink());
		$this->assertFileExists(E::getWwwDir('/assets/120x150/test.png'));

		$image = $this->storage->get('namespace/test.png', '50%x', 'fill');
		$this->assertEquals('assets/namespace/original/test.png', $image->getLink(TRUE));
		$this->assertFileExists(E::getWwwDir('/assets/namespace/original/test.png'));
		$this->assertEquals('assets/namespace/50%25x_4/test.png', $image->getLink());
		$this->assertFileExists(E::getWwwDir('/assets/namespace/50%x_4/test.png'));

		$image = $this->storage->get('namespace/namespace/test.png');
		$this->assertEquals('assets/namespace/namespace/original/test.png', $image->getLink());
	}

	public function testUpload() {
		$image = $this->storage->saveUpload($this->createUpload(), 'namespace', TRUE);
		$this->assertInstanceOf('WebChemistry\Images\Image\Upload', $image);
		$this->assertInstanceOf('WebChemistry\Images\Image\Info', $image->getInfo());

	}

	public function testContentUpload() {
		$image = $this->storage->saveContent(file_get_contents(E::getWwwDir('/test.png')), 'test.png', 'namespace');
		$this->assertInstanceOf('WebChemistry\Images\Image\Content', $image);
		$this->assertInstanceOf('WebChemistry\Images\Image\Info', $image->getInfo());

	}

	public function testMixedSize() {
		$image = $this->storage->get('test.png', 'x150|crop:50,100%,40,20');
		$this->assertEquals('assets/x150-f10b0af20d18b56ad08386bc2f103254/test.png', $image->getLink());
		$this->assertEquals(150, $image->getHeight());
		$this->assertFileExists(E::getWwwDir('/assets/x150-f10b0af20d18b56ad08386bc2f103254/test.png'));
		
		$image = $this->storage->get('test.png', '12x12|crop:50  , 100%,    40, 20');
		$this->assertEquals('assets/12x12-f10b0af20d18b56ad08386bc2f103254/test.png', $image->getLink());
		$this->assertFileExists(E::getWwwDir('/assets/12x12-f10b0af20d18b56ad08386bc2f103254/test.png'));
		
	}
	
	public function testCustomHelpers() {
		$image = $this->storage->get('test.png', 'x150|mySharpen');
		$this->assertEquals('assets/x150-bd960f16ced76e02d4f36979c8834253/test.png', $image->getLink());
		
	}

	public function testDelete() {
		$this->assertFileExists(E::getWwwDir('/assets/original/test.png'));
		$this->storage->delete('test.png');
		$this->assertFileNotExists(E::getWwwDir('/assets/original/test.png'));

		$this->assertFileExists(E::getWwwDir('/assets/namespace/original/test.png'));
		$this->assertFileExists(E::getWwwDir('/assets/namespace/250x/test.png'));
		$this->assertFileExists(E::getWwwDir('/assets/namespace/50%x_4/test.png'));
		$this->storage->delete('namespace/test.png');
		$this->assertFileNotExists(E::getWwwDir('/assets/namespace/original/test.png'));
		$this->assertFileNotExists(E::getWwwDir('/assets/namespace/250x/test.png'));
		$this->assertFileNotExists(E::getWwwDir('/assets/namespace/50%x_4/test.png'));
	}

	public function testNoImage() {
		$image = $this->storage->get('imageNotExists.png', '250x150|crop:50,100%,40,20', 'fill');
		$this->assertEquals('assets/noimage/250x150_4-f10b0af20d18b56ad08386bc2f103254/noimage.png', $image->getLink());
		$this->assertFileExists(E::getWwwDir('/assets/noimage/250x150_4-f10b0af20d18b56ad08386bc2f103254/noimage.png'));
	}

	public function testNotExistNoImage() {
		$image = $this->storage->get('imageNotExists.png', '250x150', 'fit', 'imageNotExists.png');
		$this->assertEquals(NULL, $image->getLink(FALSE, TRUE));
		$this->assertEquals('#noimage', $image->getLink());
	}

}