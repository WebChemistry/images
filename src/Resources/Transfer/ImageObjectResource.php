<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Utils\Image;
use WebChemistry\Images\Image\Image as WImage;

class ImageObjectResource extends LocalResource {

	/** @var bool|string */
	private $tmpFile;

	/**
	 * @param Image $image
	 * @param string $id
	 */
	public function __construct(Image $image, string $id) {
		$this->tmpFile = $tmp = tmpfile();
		fwrite($tmp, $image->toString(WImage::getImageType($id)));
		fseek($tmp, 0);
		$metaData = stream_get_meta_data($tmp);

		parent::__construct($metaData['uri'], $id);
	}

	/**
	 * @return void
	 */
	public function __destruct() {
		fclose($this->tmpFile);
	}

}
