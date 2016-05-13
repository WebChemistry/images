<?php

use Nette\Forms\Form;

class MultiuploadTest extends \Codeception\TestCase\Test {

	const IMAGE = 'orig-image.gif';
	const DEFAULT_IMAGE = 'default.png';

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

	// tests
	public function testUploadOne() {
		$form = $this->sendRequestToPresenter('upload');

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->isSuccess());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertSame([
			'upload' => [
				$this->getImageName()
			]
		], $form->getValues(TRUE));
	}

	public function testUploadMulti() {
		$form = $this->sendRequestToPresenter('upload', 3);

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->isSuccess());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertFileExists($this->getUploadedImage(1));
		$this->assertFileExists($this->getUploadedImage(2));
		$this->assertSame([
			'upload' => [
				$this->getImageName(),
				$this->getImageName(1),
				$this->getImageName(2)
			]
		], $form->getValues(TRUE));
	}

	public function testUploadWithDefault() {
		$this->copyImage($this->getDefaultImage());
		$form = $this->sendRequestToPresenter('upload', 1, function (Form $form) {
			$form['upload']->setDefaultValue(self::DEFAULT_IMAGE);
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->isSuccess());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertFileExists($this->getDefaultImage());
		$this->assertSame([
			'upload' => [
				self::DEFAULT_IMAGE,
				$this->getImageName()
			]
		], $form->getValues(TRUE));
	}

	public function testUploadDefaultNotExists() {
		$form = $this->sendRequestToPresenter('upload', 1, function (Form $form) {
			$form['upload']->setDefaultValue('notExists.png');
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->isSuccess());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertSame([
			'upload' => [$this->getImageName()]
		], $form->getValues(TRUE));
	}

	public function testUploadRequired() {
		$form = $this->sendRequestToPresenter('upload', 0, function (Form $form) {
			$form['upload']->setRequired();
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
	}

	public function testUploadWithFailure() {
		$form = $this->sendRequestToPresenter('upload', 1, function (Form $form) {
			$form->addText('name')->setRequired();
			$form['upload'];
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
		$this->assertFileNotExists($this->getUploadedImage());
	}

	public function testUploadRequiredWithDefaultNotExists() {
		$form = $this->sendRequestToPresenter('upload', 0, function (Form $form) {
			$form['upload']->setDefaultValue('notExists.png')->setRequired();
		});

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->hasErrors());
	}

	public function testDeleteDefaultImageWithUpload() {
		$this->copyImage($this->getDefaultImage());
		$form = $this->sendRequestToPresenter('upload', 1, function (Form $form) {
			$form['upload']->setDefaultValue(self::DEFAULT_IMAGE);
		}, [
			'upload0' . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME => TRUE
		]);

		$this->assertTrue($form->isSubmitted());
		$this->assertTrue($form->isSuccess());
		$this->assertFileExists($this->getUploadedImage());
		$this->assertFileNotExists($this->getDefaultImage());
	}

	/************************* Rendering **************************/

	public function testRender() {
		$presenter = $this->createPresenter();
		$presenter->run(new \Nette\Application\Request(''));

		$this->checkRender($presenter['upload']);
	}

	public function testRenderDefaultImage() {
		$this->copyImage($this->getDefaultImage());
		$presenter = $this->createPresenter();
		$presenter->form['upload']->setDefaultValue([self::DEFAULT_IMAGE]);
		$presenter->run(new \Nette\Application\Request(''));

		$this->checkRender($presenter['upload'], TRUE, TRUE);
	}

	public function testRenderNotExistsImage() {
		$presenter = $this->createPresenter();
		$presenter->form['upload']->setDefaultValue(['notExists.png']);
		$presenter->run(new \Nette\Application\Request(''));

		$this->checkRender($presenter['upload']);
	}

	/************************* Registration **************************/

	public function testRegistration() {
		\WebChemistry\Images\Controls\MultiUpload::register();
		$form = new Form();

		$upload = $form->addMultiImageUpload('image');
		$this->assertInstanceOf('WebChemistry\Images\Controls\MultiUpload', $upload);
	}

	/************************* Helpers **************************/

	protected function checkRender($form, $hasCheckbox = FALSE, $hasPreview = FALSE, $number = 0) {
		$html = (string) $form['upload']->getControl();

		if ($hasCheckbox) {
			$this->assertContains('type="checkbox"', $html);
			$this->assertContains('name="upload' . $number . \WebChemistry\Images\Controls\Checkbox::CHECKBOX_NAME . '"', $html);
		} else {
			$this->assertNotContains('type="checkbox"', $html);
			$this->assertNotRegExp('#name="upload[0-9]+#', $html);
		}

		if ($hasPreview) {
			$this->assertContains('<img', $html);
			$this->assertContains('class="preview-image-container"', $html);
		} else {
			$this->assertNotContains('<img', $html);
			$this->assertNotContains('class="preview-image-container"', $html);
		}
	}

	protected function getImageName($number = NULL) {
		return "orig-image$number.gif";
	}

	protected function getDefaultImage() {
		return $this->assetsDir . '/original/' . self::DEFAULT_IMAGE;
	}

	protected function getUploadedImage($number = NULL) {
		return $this->assetsDir . "/original/orig-image$number.gif";
	}

	protected function copyImage($dest) {
		@copy(__DIR__ . '/../_data/image.gif', $dest);
	}

	/**
	 * @param string $name
	 * @return \Nette\Application\UI\Presenter
	 */
	protected function createPresenter($name = 'MultiUpload') {
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

	protected function sendRequestToPresenter($controlName = 'upload', $upload = 1, $factory = NULL, $post = []) {
		$presenter = $this->createPresenter('MultiUpload');

		if (is_callable($factory)) {
			$factory($presenter->getForm());
		}

		$uploads = [];
		for ($i = 0; $i < $upload; $i++) {
			$uploads[] = $this->createUpload($i ? $i : NULL);
		}

		$presenter->run(new \Nette\Application\Request('MultiUpload', 'POST', [
			'do' => $controlName . '-submit'
		], array_merge([
			'submit' => 'submit'
		], $post), [
			'upload' => $uploads
		]));

		/** @var \Nette\Application\UI\Form $form */
		$form = $presenter[$controlName];

		return $form;
	}

	private function createUpload($number = NULL) {
		$dest =  __DIR__ . "/../_data/assets/original/image$number.gif";
		$this->copyImage($dest);
		return new \Nette\Http\FileUpload([
			'tmp_name' => $dest,
			'type' => 'image/gif',
			'size' => 15,
			'error' => 0,
			'name' => $this->getImageName($number)
		]);
	}

}
