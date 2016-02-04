<?php

class FolderMock extends \WebChemistry\Images\FileStorage\Image\Folders {

	public function delete() {}

	public function getLink() {}

	public function save(Nette\Utils\Image $image, $imageType = NULL) {}

	public function saveUpload(Nette\Http\FileUpload $image, $imageType = NULL) {}

}

class FoldersTest extends \Codeception\TestCase\Test {

    /** @var FolderMock */
    protected $folder;

    protected function _before() {
        $this->folder = $this->create();
    }

	protected function create($fill = TRUE) {
		$folder = new FolderMock(__DIR__ . '/../_data', 'assets');

		if ($fill) {
			$folder->setNamespace('namespace');
			$folder->setName('file.jpg');
			$folder->setMixedSize('250x150');
			$folder->setFlag('exact');
			$folder->setPrefix('myprefix');
		}

		return $folder;
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

	public function testAbsolutePath() {
		$this->assertSame(__DIR__ . '/../_data/assets/namespace/250x150_8/myprefix' . FolderMock::PREFIX_SEP . 'file.jpg',
			$this->folder->getAbsolutePath());
	}

	public function testRelativePath() {
		$this->assertSame('assets/namespace/250x150_8/myprefix' . FolderMock::PREFIX_SEP . 'file.jpg',
			$this->folder->getRelativePath());
	}

	public function testOriginalPath() {
		$this->assertSame(__DIR__ . '/../_data/assets/namespace/' . FolderMock::ORIGINAL . '/myprefix' . FolderMock::PREFIX_SEP . 'file.jpg',
			$this->folder->getOriginalPath());
	}

	public function testHelpers() {
		$this->folder->addHelper(new WebChemistry\Images\Helpers\Sharpen, 'sharpen');
		$this->folder->addHelper(new WebChemistry\Images\Helpers\Crop, 'crop');
		$this->folder->parseHelpers(['sharpen', 'crop:20, 50, 150']);
		$hash = md5('WebChemistry\Images\Helpers\SharpenWebChemistry\Images\Helpers\Crop20,50,150');
		$this->assertSame(__DIR__ . '/../_data/assets/namespace/250x150_8-' . $hash . '/myprefix' . FolderMock::PREFIX_SEP . 'file.jpg',
			$this->folder->getAbsolutePath());
	}

	public function testDefaultImage() {
		$this->folder->setDefaultImage('default/default.png');
		$default = $this->folder->getDefaultImageClass();

		$this->assertInstanceOf('WebChemistry\\Images\\Image\\PropertyAccess', $default);
		$this->assertSame(__DIR__ . '/../_data/assets/default/250x150_8/default.png', $default->getAbsolutePath());
	}

	public function testIsExists() {
		$folder = $this->create(FALSE);
		$folder->setName('image.gif');

		$this->assertTrue($folder->isExists());
	}

	public function testPrefix() {
		$folder = $this->create(FALSE);

		$this->assertNull($folder->getPrefix());

		$folder->setName('image.gif');
		$folder->generateUniqueImageName();

		$this->assertNotNull($folder->getPrefix());
	}

}
