<?php

class UploadTest extends \Codeception\TestCase\Test {

	const IMAGE = 'orig-image.gif';

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/** @var string */
	protected $assetsDir;

	protected function _before() {
		$this->assetsDir = __DIR__ . '/../_data/tmp';
	}

	protected function _after() {
		Helper::removeFilesRecursive(__DIR__ . '/../_data/tmp');
	}

	public function testFilled() {
		$form = $this->sendRequestToPresenter();

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertSame(self::IMAGE, $form['upload']->getValue());
	}

	public function testNotFilled() {
		$form = $this->sendRequestToPresenter('upload', FALSE);

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertNull($form['upload']->getValue());
	}

	public function testFilledWithDefaultValue() {
		$this->copyImage($this->getDefaultImage());

		$form = $this->sendRequestToPresenter('upload', TRUE, function ($form) {
			$form['upload']->setDefaultValue('default.png');
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertFileNotExists($this->getDefaultImage());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertSame(self::IMAGE, $form['upload']->getValue());
	}

	public function testNotFilledWithDefaultValue() {
		$this->copyImage($this->getDefaultImage());

		$form = $this->sendRequestToPresenter('upload', FALSE, function ($form) {
			$form['upload']->setDefaultValue('default.png');
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertFileExists($this->getDefaultImage());
		$this->assertSame('default.png', $form['upload']->getValue());
	}

	public function testNotFilledWithDefaultValueCheckedCheckbox() {
		$this->copyImage($this->getDefaultImage());

		$form = $this->sendRequestToPresenter('upload', FALSE, function ($form) {
			$form['upload']->setDefaultValue('default.png');
		}, [
			'upload' . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME => TRUE
		]);

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertFileNotExists($this->getDefaultImage());
		$this->assertNull($form['upload']->getValue());
	}

	public function testFilledWithError() {
		$form = $this->sendRequestToPresenter('upload', TRUE, function ($form) {
			$form['upload']->setDefaultValue('default.png')->addRule(\Nette\Forms\Form::MAX_FILE_SIZE, NULL, 1);
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertFileNotExists($this->getUploadedImage());
		$this->assertInstanceOf('Nette\Http\FileUpload', $form['upload']->getValue());
	}

	public function testFilledWithCheckboxWithError() {
		$this->copyImage($this->getDefaultImage());

		$form = $this->sendRequestToPresenter('upload', TRUE, function ($form) {
			$form['upload']->setDefaultValue('default.png')->addRule(\Nette\Forms\Form::MAX_FILE_SIZE, NULL, 1);
		}, [
			'upload' . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME => TRUE
		]);

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertFileExists($this->getDefaultImage());
		$this->assertInstanceOf('Nette\Http\FileUpload', $form['upload']->getValue());
	}

	public function testFilledErrorAfterSuccesCallback() {
		$form = $this->sendRequestToPresenter('upload', TRUE, function ($form) {
			$form->onSuccess[] = function ($form) {
				$form->addError('');
			};
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertFalse($form->isValid());
		$this->assertFileNotExists($this->getUploadedImage());
	}

	/************************* Required upload control **************************/

	public function testRequiredNotFilled() {
		$form = $this->sendRequestToPresenter('upload', FALSE, function ($form) {
			$form['upload']->setRequired();
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertFileNotExists($this->getUploadedImage());
		$this->assertInstanceOf('Nette\Http\FileUpload', $form['upload']->getValue());
	}

	public function testRequiredNotFilledDefaultValueNotExists() {
		$form = $this->sendRequestToPresenter('upload', FALSE, function ($form) {
			$form['upload']->setRequired()->setDefaultValue('notExists.png');
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertInstanceOf('Nette\Http\FileUpload', $form['upload']->getValue());
	}

	public function testRequiredNotFilledWithDefaultValue() {
		$this->copyImage($this->getDefaultImage());
		$form = $this->sendRequestToPresenter('upload', FALSE, function ($form) {
			$form['upload']->setRequired()->setDefaultValue('default.png');
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertFalse($form->hasErrors());
		$this->assertFileExists($this->getDefaultImage());
		$this->assertSame('default.png', $form['upload']->getValue());
	}

	/************************* Render **************************/

	public function testRender() {
		$presenter = $this->createPresenter('Upload');
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload']);
	}

	public function testRenderImageNotExists() {
		$presenter = $this->createPresenter('Upload');
		$presenter->form['upload']->setDefaultValue('notExists.png');
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload']);
	}

	public function testRenderImageExists() {
		$this->copyImage($this->getDefaultImage());
		$presenter = $this->createPresenter('Upload');
		$presenter->form['upload']->setDefaultValue('default.png');
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload'], TRUE, TRUE);
	}

	public function testRenderRequired() {
		$presenter = $this->createPresenter('Upload');
		$presenter->form['upload']->setRequired();
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload']);
	}

	public function testRenderRequiredImageExists() {
		$this->copyImage($this->getDefaultImage());
		$presenter = $this->createPresenter('Upload');
		$presenter->form['upload']->setRequired()->setDefaultValue('default.png');
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload'], FALSE, TRUE);
	}

	public function testRenderRequiredImageNotExists() {
		$presenter = $this->createPresenter('Upload');
		$presenter->form['upload']->setRequired()->setDefaultValue('notExists.png');
		$presenter->run(new \Nette\Application\Request('Upload'));

		$this->checkRender($presenter['upload']);
	}

	/************************* Registration **************************/

	public function testRegistration() {
		\WebChemistry\Images\Controls\Upload::register();
		$form = new \Nette\Forms\Form();

		$upload = $form->addImageUpload('image');
		$this->assertInstanceOf('WebChemistry\Images\Controls\Upload', $upload);
	}

	/************************* Events **************************/

	public function testOnBeforeSave() {
		$isCalled = FALSE;
		$form = $this->sendRequestToPresenter('upload', TRUE, function (\Nette\Forms\Form $form) use (&$isCalled) {
			$form['upload']->onBeforeSave[] = function (\Nette\Utils\Image $image) use (&$isCalled) {
				$isCalled = TRUE;

				$image->resize(1,1);
				
				return $image;
			};
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($isCalled);
		$this->assertTrue($isCalled);
		$this->assertFalse($form->hasErrors());
		$this->assertFileExists($this->getUploadedImage());
		$size = getimagesize($this->getUploadedImage());
		$this->assertSame(1, $size[0]);
		$this->assertSame(1, $size[1]);
		$this->assertSame(self::IMAGE, $form['upload']->getValue());
	}

	/************************* Helpers **************************/

	protected function checkRender($form, $hasCheckbox = FALSE, $hasPreview = FALSE) {
		$html = (string) $form['upload']->getControl();

		if ($hasCheckbox) {
			$this->assertContains('type="checkbox"', $html);
			$this->assertContains('name="upload' . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME . '"', $html);
		} else {
			$this->assertNotContains('type="checkbox"', $html);
			$this->assertNotContains('name="upload' . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME . '"', $html);
		}

		if ($hasPreview) {
			$this->assertContains('<img', $html);
			$this->assertContains('class="preview-image-container"', $html);
		} else {
			$this->assertNotContains('<img', $html);
			$this->assertNotContains('class="preview-image-container"', $html);
		}
	}

	protected function getDefaultImage() {
		return $this->assetsDir . '/original/default.png';
	}

	protected function getUploadedImage() {
		return $this->assetsDir . '/original/' . self::IMAGE;
	}

	protected function copyImage($dest) {
		@copy(__DIR__ . '/../_data/image.gif', $dest);
	}

	/**
	 * @param string $name
	 * @return \Nette\Application\UI\Presenter
	 */
	protected function createPresenter($name) {
		$presenterFactory = new \Nette\Application\PresenterFactory(function ($class) {
			/** @var \Nette\Application\UI\Presenter $presenter */
			$presenter = new $class();
			$presenter->injectPrimary(new \Nette\DI\Container(), NULL, NULL,
				new \Nette\Http\Request(new \Nette\Http\UrlScript()), new \Nette\Http\Response(), NULL, NULL,
				new MockLatte());
			$presenter->autoCanonicalize = FALSE;
			$presenter->imageStorage = new \WebChemistry\Images\FileStorage\FileStorage('image.gif', [
				'helpers' => [
					'sharpen' => new \WebChemistry\Images\Helpers\Sharpen()
				],
				'wwwDir' => __DIR__ . '/../_data',
				'assetsDir' => 'tmp',
				'defaultImage' => 'image.gif'
			] + (new \WebChemistry\Images\DI\Extension)->defaults, new \Nette\Http\Request(new \Nette\Http\UrlScript()));

			return $presenter;
		});

		return $presenterFactory->createPresenter($name);
	}

	protected function sendRequestToPresenter($controlName = 'upload', $upload = TRUE, $factory = NULL, $post = []) {
		$presenter = $this->createPresenter('Upload');
		$upload = $upload ? $this->createUpload() : NULL;

		if (is_callable($factory)) {
			$factory($presenter->getForm());
		}

		$presenter->run(new \Nette\Application\Request('Upload', 'POST', [
			'do' => $controlName . '-submit'
		], array_merge([
			'submit' => 'submit'
		], $post), [
			'upload' => $upload
		]));

		/** @var \Nette\Application\UI\Form $form */
		$form = $presenter[$controlName];

		return $form;
	}

	private function createUpload() {
		@copy(__DIR__ . '/../_data/image.gif', __DIR__ . '/../_data/assets/original/image.gif'); // move in FileUpload
		return new \Nette\Http\FileUpload([
			'tmp_name' => __DIR__ . '/../_data/assets/original/image.gif',
			'type' => 'image/gif',
			'size' => 15,
			'error' => 0,
			'name' => self::IMAGE
		]);
	}

}
