<?php

class TexyTest extends \Codeception\TestCase\Test {

	/** @var Texy */
	protected $texy;

	protected function _before() {
		$this->texy = new Texy();

		E::createDirs('%www%/assets', [
			'original',
			'namespace/200x100_8'
		]);

		E::copy('%data%/test.png', [
			'%www%/assets/original/%name%',
			'%www%/assets/namespace/200x100_8/image.png'
		]);

		\WebChemistry\Images\Texy::register($this->texy, E::getByType('WebChemistry\Images\Storage'), '', '/baseUri');
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @since Method available since Release 3.4.0
	 */
	public static function tearDownAfterClass() {
		E::truncateDirectory('%www%/assets');
	}

	public function testMe() {
		$content = "[img namespace/image.png, 200x100, exact]:(alt = Popis obrázku, class = img-responsive img-circle)
					[img test.png][img //test.png]";

		$result = $this->texy->process($content);

		$this->assertStringEqualsFile(E::dumpedFile('texy'), $result);
	}
}