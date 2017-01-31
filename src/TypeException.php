<?php

namespace WebChemistry\Images;


class TypeException extends \Exception {

	public function __construct($type, $given) {
		$given = is_object($given) ? get_class($given) : gettype($given);
		$this->message = sprintf($type . ' expected, %s given.', $given);
	}

}
