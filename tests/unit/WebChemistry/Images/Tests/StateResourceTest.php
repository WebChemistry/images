<?php

namespace WebChemistry\Images\Tests;

use Test\StateTester;
use WebChemistry\Images\Resources\FileResource;
use WebChemistry\Images\Resources\StateResource;
use WebChemistry\Images\Resources\Transfer\LocalResource;

class StateResourceTest extends \Codeception\Test\Unit {

	protected function _before() {

	}

	protected function _after() {

	}

	public function testStateResource() {
		$default = new FileResource('default.jpg');
		$upload = new LocalResource(IMAGE_GIF, 'image.gif');

		$state = new StateTester($this);

		$state->addState('default, no upload, no delete => default')
			->params($default, null, false)
			->expect(null, null, $default);

		$state->addState('default, upload, no delete => delete and upload')
			->params($default, $upload, false)
			->expect($upload, $default, null);

		$state->addState('no default, upload, no delete => upload')
			->params(null, $upload, false)
			->expect($upload, null, null);

		$state->addState('no default, no upload, no delete => nothing')
			->params(null, null, false)
			->expect(null, null, null);

		$state->addState('default, no upload, delete => delete')
			->params($default, null, true)
			->expect(null, $default, null);

		$state->addState('no default, no upload, delete => nothing')
			->params(null, null, true)
			->expect(null, null, null);


		$state->call(function (array $params, array $expect) {
			$state = new StateResource(...$params);

			[$upload, $delete, $default] = $expect;

			$this->assertSame($upload, $state->getUpload());
			$this->assertSame($delete, $state->getDelete());
			$this->assertSame($default, $state->getDefaultValue());
		});
	}

}
