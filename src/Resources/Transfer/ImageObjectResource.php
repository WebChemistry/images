<?php

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Utils\Image;

class ImageObjectResource extends LocalResource {

	/** @var bool|string  */
	private $tmpFile;

	/**
	 * @param \Nette\Utils\Image    $image
	 * @param string                $id
	 */
	public function __construct(Image $image, $id) {
		$this->tmpFile = $tmp = tmpfile();
		fwrite($tmp, $image->toString());
		fseek($tmp, 0);
		$metaData = stream_get_meta_data($tmp);

		parent::__construct($metaData['uri'], $id);
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		fclose($this->tmpFile);
	}
}
