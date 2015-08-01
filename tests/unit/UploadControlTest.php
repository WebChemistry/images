<?php

class UploadControlTest extends \Codeception\TestCase\Test {

	protected function _before() {
		E::createDirs('%www%/assets', [
			'namespace/original'
		]);

		E::copy('%data%/test.png', [
			'%www%/assets/namespace/original/%name%'
		]);
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

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @since Method available since Release 3.4.0
	 */
	public static function tearDownAfterClass() {
		E::truncateDirectory('%www%/assets');
	}

	public function testTemplate() {
		/** @var \Nette\Application\IPresenterFactory $presenterFactory */
		$presenterFactory = E::getByType('Nette\Application\IPresenterFactory');

		/** @var \Test\Presenters\UploadPresenter $presenter */
		$presenter = $presenterFactory->createPresenter('Upload');

		$response = $presenter->run(new \Nette\Application\Request('Upload', 'GET', [
			'form' => 'template'
		]));

		$content = (string) $response->getSource();

		$this->assertStringEqualsFile(E::dumpedFile('baseForm'), $content);
	}

	public function testUpload() {
		/** @var \Nette\Application\IPresenterFactory $presenterFactory */
		$presenterFactory = E::getByType('Nette\Application\IPresenterFactory');

		/** @var \Test\Presenters\UploadPresenter $presenter */
		$presenter = $presenterFactory->createPresenter('Upload');

		$presenter->run(new \Nette\Application\Request('Upload', 'POST', [
			'do' => 'upload-submit'
		], [
			'upload' => $this->createUpload(),
			'uploadNamespace' => $this->createUpload(),
			'prefix' => $this->createUpload()
		]));

		$values = $presenter['upload']->getValues(TRUE);

		$this->assertSame('test-upload.png', $values['upload']);
		$this->assertNull($values['null']);
		$this->assertSame('upload/test-upload.png', $values['uploadNamespace']);
		$this->assertNotSame('test-upload.png', $values['prefix']);
		$this->assertContains('_._test-upload.png', $values['prefix']);

		$this->assertFileExists(E::directory('%www%/assets/original/test-upload.png'));
		$this->assertFileExists(E::directory('%www%/assets/upload/original/test-upload.png'));
		$this->assertFileExists(E::directory('%www%/assets/original/' . $values['prefix']));
	}

	public function testDelete() {
		E::createDirs('%www%/assets', [
			'delete/original'
		]);
		E::copy('%data%/test.png', [
			'%www%/assets/delete/original/%name%'
		]);

		$this->assertFileExists(E::directory('%www%/assets/delete/original/test.png'));

		/** @var \Nette\Application\IPresenterFactory $presenterFactory */
		$presenterFactory = E::getByType('Nette\Application\IPresenterFactory');

		/** @var \Test\Presenters\UploadPresenter $presenter */
		$presenter = $presenterFactory->createPresenter('Upload');

		$presenter->run(new \Nette\Application\Request('Upload', 'POST', [
			'do' => 'delete-submit'
		], [
			'upload_checkbox_image_delete' => TRUE
		]));

		$values = $presenter['delete']->getValues();

		$this->assertNull($values->upload);

		$this->assertFileNotExists(E::directory('%www%/assets/delete/original/test.png'));
	}

	public function testRequireUploadDelete() {
		E::createDirs('%www%/assets', [
			'delete/original'
		]);
		E::copy('%data%/test.png', [
			'%www%/assets/delete/original/%name%'
		]);

		/** @var \Nette\Application\IPresenterFactory $presenterFactory */
		$presenterFactory = E::getByType('Nette\Application\IPresenterFactory');

		/** @var \Test\Presenters\UploadPresenter $presenter */
		$presenter = $presenterFactory->createPresenter('Upload');

		$presenter->run(new \Nette\Application\Request('Upload', 'POST', [
			'do' => 'requireUpload-submit'
		], [
			'upload_checkbox_image_delete' => TRUE
		]));

		$values = $presenter['requireUpload']->getValues();

		$this->assertSame('delete/test.png', $values->upload);
	}

	public function testRequireUpload() {
		E::createDirs('%www%/assets', [
			'delete/original'
		]);
		E::copy('%data%/test.png', [
			'%www%/assets/delete/original/%name%'
		]);

		/** @var \Nette\Application\IPresenterFactory $presenterFactory */
		$presenterFactory = E::getByType('Nette\Application\IPresenterFactory');

		/** @var \Test\Presenters\UploadPresenter $presenter */
		$presenter = $presenterFactory->createPresenter('Upload');

		$presenter->run(new \Nette\Application\Request('Upload', 'POST', [
			'do' => 'requireUpload-submit'
		], [
			'upload' => $this->createUpload(),
			'upload_checkbox_image_delete' => TRUE
		]));

		$values = $presenter['requireUpload']->getValues();

		$this->assertSame('delete/test-upload.png', $values->upload);
	}
}