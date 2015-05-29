<?php

class MySharpen {

	public function __construct(\Nette\DI\Container $container) {

	}

	public function invoke(&$image) {
		$image->sharpen();
	}


}