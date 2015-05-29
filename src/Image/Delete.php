<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Delete extends Container {

	public function delete() {
		$this->connector->delete($this->getInfo());
	}
}
