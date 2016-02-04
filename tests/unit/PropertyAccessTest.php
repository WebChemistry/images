<?php

class PropertyMock extends \WebChemistry\Images\Image\PropertyAccess {

	public function getHash() {
		return parent::getHash();
	}

	public function getAbsolutePath() {}

	public function getRelativePath() {}

	public function delete() {}

	public function getLink() {}

	public function isExists() {}

	public function save(Nette\Utils\Image $image, $imageType = NULL) {}

	public function saveUpload(Nette\Http\FileUpload $image, $imageType = NULL) {}

}

class PropertyAccessTest extends \Codeception\TestCase\Test {

	/** @var PropertyMock */
	protected $property;

    protected function _before() {
        $this->property = new PropertyMock();
    }

	public function assertException($callback, $exception = 'Exception') {
		try {
			$callback();
		} catch (\Exception $e) {
			if ($e instanceof $exception) {
				return TRUE;
			}

			$this->fail(printf('Expected %s excepetion. Given %s.', $exception, get_class($e)));
		}

		$this->fail('Expected exception. None given.');
	}

    protected function _after()
    {
    }

    public function testNamespace() {
        $this->assertSame('namespace', $this->property->setNamespace('namespace')->getNamespace());
        $this->assertSame('namespace/images', $this->property->setNamespace('namespace/images')->getNamespace());
    }

	public function testName() {
		$this->assertSame('filename.jpg', $this->property->setName('filename.jpg')->getName());
		$this->assertException(function () {
			$this->property->setName('filename');
		});
	}

	public function testSuffix() {
		$this->assertSame('filename.jpg', $this->property->setNameWithoutSuffix('filename.jpg')->getName());
		$this->assertSame('filename.jpg', $this->property->setNameWithoutSuffix('filename.jpg')->getNameWithoutSuffix());
		$this->assertSame('filename', $this->property->setName('filename.jpg')->getNameWithoutSuffix());

		$this->_before();
		$this->assertSame('filename', $this->property->setNameWithoutSuffix('filename')->getName());
		$this->assertSame('filename.jpg', $this->property->setSuffix('jpg')->getName());
	}

	public function testAbsoluteName() {
		$this->assertSame('namespace/namespace/filename.jpg', $this->property->setAbsoluteName
		('namespace/namespace/filename.jpg')->getAbsoluteName());
		$this->assertSame('filename.jpg', $this->property->setAbsoluteName
		('namespace/namespace/filename.jpg')->getName());
		$this->assertSame('namespace/namespace', $this->property->setAbsoluteName
		('namespace/namespace/filename.jpg')->getNamespace());
	}

	public function testQuality() {
		$this->assertException(function () {
			$this->property->setQuality(120);
		});

		$this->assertException(function () {
			$this->property->setQuality('asd');
		});

		$this->assertSame(70, $this->property->setQuality(70)->getQuality());
	}

	public function testPrefix() {
		$this->assertSame('prefix', $this->property->setPrefix('prefix')->getPrefix());
		$this->assertSame('prefix_._name.jpg', $this->property->setName('name.jpg')->getNameWithPrefix());
	}

	public function testFlags() {
		$this->assertSame(0, $this->property->setIntegerFlag(0)->getFlag());
		$this->assertSame(0, $this->property->setFlag('fit')->getFlag());
		$this->assertSame(8, $this->property->setFlag('exact')->getFlag());
	}

	public function testWidth() {
		$this->assertSame(250, $this->property->setWidth(250)->getWidth());
		$this->assertSame('120%', $this->property->setWidth('120%')->getWidth());

		$this->assertException(function () {
			$this->property->setWidth('test');
		});

		$this->assertSame(250, $this->property->setMixedSize('250x')->getWidth());
		$this->assertSame(250, $this->property->setMixedSize('250')->getWidth());
		$this->assertSame('120%', $this->property->setMixedSize('120%')->getWidth());
		$this->assertSame('120%', $this->property->setMixedSize('120%x150%')->getWidth());
	}

	public function testHeight() {
		$this->assertSame(250, $this->property->setHeight(250)->getHeight());
		$this->assertSame('120%', $this->property->setHeight('120%')->getHeight());

		$this->assertException(function () {
			$this->property->setHeight('test');
		});

		$this->assertSame(250, $this->property->setMixedSize('x250')->getHeight());
		$this->assertSame('120%', $this->property->setMixedSize('x120%')->getHeight());
		$this->assertSame('150%', $this->property->setMixedSize('120%x150%')->getHeight());
	}

	public function testMixedSize() {
		$this->assertException(function () {
			$this->property->setMixedSize('250x250x250');
		});
	}

	public function testBaseUri() {
		$this->assertFalse($this->property->isBaseUri());

		$this->assertTrue($this->property->setAbsoluteName('//namespace/file.jpg')->isBaseUri());
	}

	public function testHelpers() {
		$this->property->addHelper(new WebChemistry\Images\Helpers\Sharpen, 'sharpen');
		$this->property->addHelper(new WebChemistry\Images\Helpers\Crop, 'crop');

		$this->assertNull($this->property->getHash());

		$this->property->parseHelpers(['sharpen', 'crop:20, 50, 150']);
		$this->assertSame(md5('WebChemistry\Images\Helpers\SharpenWebChemistry\Images\Helpers\Crop20,50,150'), $this->property->getHash());
	}

	public function testDefaultImage() {
		$this->assertException(function () {
			$this->property->getDefaultImageClass(); // Default image does not exist.
		});

		$this->property->setDefaultImage('default/default.jpg');
		$default = $this->property->getDefaultImageClass();

		$this->assertNull($default->getDefaultImage());
	}

	public function testOriginalHelpers() {
		$this->property->addHelper(new WebChemistry\Images\Helpers\Sharpen, 'sharpen');

		$this->property->parseHelpers(['sharpen']);
		$original = $this->property->getOriginalClass();

		$this->assertNull($original->getHash());
	}

	public function testOriginal() {
		$this->property->setName('name.jpg');
		$this->property->setNamespace('namespace');
		$this->property->setDefaultImage('default/default.png');
		$this->property->setQuality(100);

		$this->assertTrue($this->property->isOriginal());

		$this->property->setWidth(150);

		$this->assertFalse($this->property->isOriginal());
	}

}
