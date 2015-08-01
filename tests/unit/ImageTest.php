<?php

class ImageTest extends \Codeception\TestCase\Test {

	/** @var \WebChemistry\Images\Storage */
	private $storage;

	protected function _before() {
		$this->storage = E::getByType('WebChemistry\Images\Storage');

		E::createDirs('%www%', ['assets']);

		E::createDirs('%www%/assets', [
			'original',
			'namespace/original',
			'namespace/namespace/original',
			'namespace/250x',
			'noimage/original'
		]);

		E::copy('%data%/test.png', [
			'%www%/assets/original/%name%',
			'%www%/assets/namespace/original/%name%',
			'%www%/assets/namespace/namespace/original/%name%',
			'%www%/assets/namespace/250x/%name%',
			'%www%/assets/noimage/original/noimage.png'
		]);
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @since Method available since Release 3.4.0
	 */
	public static function tearDownAfterClass() {
		E::truncateDirectory('%www%/assets');
	}

	private function createUpload() {
		return new \Nette\Http\FileUpload([
			'name' => 'test-upload.png',
			'tmp_name' => E::directory('%data%/test.png'),
			'type' => 'image/jpeg',
			'size' => 6502,
			'error' => 0
		]);
	}

	public function testImageClass() {
		$image = $this->storage->get('namespace/name.jpg', '140x150', 'fill');
		$this->assertInstanceOf('WebChemistry\Images\Image\Image', $image);
		$this->assertEquals(4, $image->getFlag());
		$this->assertEquals('namespace', $image->getNamespace());
		$this->assertEquals('name.jpg', $image->getName());
		$this->assertEquals(140, $image->getWidth());
		$this->assertEquals(150, $image->getHeight());
		$this->assertEquals('jpg', $image->getSuffix());
		$this->assertEquals('name', $image->getNameWithoutSuffix());


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

	public function testSetting() {
		$image = $this->storage->get('namespace/name.jpg', '140x150', 'fill');

		$image->setSuffix('suf');

		$this->assertEquals('name.suf', $image->getName());
		$this->assertEquals('name', $image->getNameWithoutSuffix());
		$this->assertEquals('suf', $image->getSuffix());

		$image->setNameWithoutSuffix('my');

		$this->assertEquals('my.suf', $image->getName());
		$this->assertEquals('my', $image->getNameWithoutSuffix());
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
			0 => 16, 1 => 16, 2 => 1, 3 => 'width="16" height="16"', 'bits' => 5, 'channels' => 3, 'mime' => 'image/gif'
		), $info->getImageSize());
		$this->assertSame(1, $info->getImageType());
	}

	public function testCreateImage() {
		$image = $this->storage->get('test.png', '120x150');
		$info = $image->getInfo();

		$this->assertEquals('assets/original/test.png', $image->getLink(TRUE));
		$this->assertFileExists(E::directory('%www%/assets/original/test.png'));
		$this->assertEquals('assets/120x150/test.png', $image->getLink());
		$this->assertFileExists(E::directory('%www%/assets/120x150/test.png'));

		$image = $this->storage->get('namespace/test.png', '50%x', 'fill');
		$this->assertEquals('assets/namespace/original/test.png', $image->getLink(TRUE));
		$this->assertFileExists(E::directory('%www%/assets/namespace/original/test.png'));
		$this->assertEquals('assets/namespace/50%25x_4/test.png', $image->getLink());
		$this->assertFileExists(E::directory('%www%/assets/namespace/50%x_4/test.png'));

		$image = $this->storage->get('namespace/namespace/test.png');
		$this->assertEquals('assets/namespace/namespace/original/test.png', $image->getLink());
	}

	public function testUpload() {
		$image = $this->storage->saveUpload($this->createUpload(), 'namespace', TRUE);
		$this->assertInstanceOf('WebChemistry\Images\Image\Upload', $image);
		$info = $image->getInfo();
		$this->assertInstanceOf('WebChemistry\Images\Image\Info', $info);
		$this->assertSame('namespace/test-upload.png', (string) $info);
		$this->assertNull($info->getPrefix());

		// Image with prefix
		$image = $this->storage->saveUpload($this->createUpload(), 'namespace', TRUE);
		$info = $image->getInfo();
		$this->assertNotNull($info->getPrefix());
		$this->assertSame('namespace/' . $info->getPrefix() . \WebChemistry\Images\Image\Info::PREFIX_SEP . 'test-upload.png', $info->getAbsoluteName());
	}

	public function testContentUpload() {
		$image = $this->storage->saveContent(file_get_contents(E::directory('%data%/test.png')), 'test.png', 'namespace_content');
		$this->assertInstanceOf('WebChemistry\Images\Image\Content', $image);
		$info = $image->getInfo();
		$this->assertInstanceOf('WebChemistry\Images\Image\Info', $info);
		$this->assertNull($info->getPrefix());

		// Image with prefix
		$image = $this->storage->saveContent(file_get_contents(E::directory('%data%/test.png')), 'test.png', 'namespace_content');
		$info = $image->getInfo();
		$this->assertNotNull($info->getPrefix());
		$this->assertSame('namespace_content/' . $info->getPrefix() . \WebChemistry\Images\Image\Info::PREFIX_SEP . 'test.png', $info->getAbsoluteName());
	}

	public function testMixedSize() {
		$image = $this->storage->get('test.png', 'x150|crop:2,2,40,20');
		$this->assertEquals('assets/x150-3301bf6e70fa9b6c83711fe0399be601/test.png', $image->getLink());
		$this->assertEquals(150, $image->getHeight());
		$this->assertFileExists(E::directory('%www%/assets/x150-3301bf6e70fa9b6c83711fe0399be601/test.png'));

		$image = $this->storage->get('test.png', '12x12|crop:2  , 2,    40, 20');
		$this->assertEquals('assets/12x12-3301bf6e70fa9b6c83711fe0399be601/test.png', $image->getLink());
		$this->assertFileExists(E::directory('%www%/assets/12x12-3301bf6e70fa9b6c83711fe0399be601/test.png'));
	}

	public function testCustomHelpers() {
		$image = $this->storage->get('test.png', 'x150|mySharpen');
		$this->assertEquals('assets/x150-bd960f16ced76e02d4f36979c8834253/test.png', $image->getLink());
	}

	public function testDelete() {
		$this->assertFileExists(E::directory('%www%/assets/original/test.png'));
		$this->storage->delete('test.png');
		$this->assertFileNotExists(E::directory('%www%/assets/original/test.png'));

		$this->assertFileExists(E::directory('%www%/assets/namespace/original/test.png'));
		$this->assertFileExists(E::directory('%www%/assets/namespace/250x/test.png'));
		$this->assertFileExists(E::directory('%www%/assets/namespace/50%x_4/test.png'));
		$this->storage->delete('namespace/test.png');
		$this->assertFileNotExists(E::directory('%www%/assets/namespace/original/test.png'));
		$this->assertFileNotExists(E::directory('%www%/assets/namespace/250x/test.png'));
		$this->assertFileNotExists(E::directory('%www%/assets/namespace/50%x_4/test.png'));
	}

	public function testNoImage() {
		$image = $this->storage->get('imageNotExists.png', '250x150|crop:1,1,40,20', 'fill');
		$this->assertEquals('assets/noimage/250x150_4-12a4646c53cbb2e1ea991cd6c9b2c042/noimage.png', $image->getLink());
		$this->assertFileExists(E::directory('%www%/assets/noimage/250x150_4-12a4646c53cbb2e1ea991cd6c9b2c042/noimage.png'));
	}

	public function testNotExistNoImage() {
		$image = $this->storage->get('imageNotExists.png', '250x150', 'fit', 'imageNotExists.png');
		$this->assertEquals(NULL, $image->getLink(FALSE, TRUE));
		$this->assertEquals('#noimage', $image->getLink());
	}

	public function testMultiUploadSave() {
		$upload = $this->storage->saveUpload($this->createUpload(), 'namespace_double', FALSE);

		$upload->save();

		$this->assertFileExists(E::directory('%www%/assets/namespace_double/original/test-upload.png'));

		$upload->setNameWithoutSuffix('myName');

		$upload->save();

		$this->assertFileExists(E::directory('%www%/assets/namespace_double/original/myName.png'));

		$upload->setPrefix('myPrefix');
		$upload->setWidth(5);
		$upload->setHeight(5);
		$upload->setSuffix('jpg');

		$upload->save();

		$this->assertFileExists(E::directory('%www%/assets/namespace_double/5x5/myPrefix_._myName.jpg'));

		$sizeInfo = $upload->getInfo()->getImageSize();
		$this->assertEquals(5, $sizeInfo[0]);
		$this->assertEquals(5, $sizeInfo[1]);
	}

	public function testMultiContentSave() {
		$upload = $this->storage->saveContent(file_get_contents(E::directory('%data%/test.gif')), 'name.gif', 'content_multi', FALSE);

		$upload->save();

		$this->assertFileExists(E::directory('%www%/assets/content_multi/original/name.gif'));

		$upload->setNameWithoutSuffix('myName');
		$upload->setWidth(5);
		$upload->setHeight(5);

		$upload->save();

		$this->assertFileExists(E::directory('%www%/assets/content_multi/5x5/myName.gif'));
	}
}
