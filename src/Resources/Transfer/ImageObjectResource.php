<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resources\Transfer;

use Nette\Utils\Image;
use RuntimeException;
use WebChemistry\Images\Image\Image as WImage;

class ImageObjectResource extends LocalResource {

	/** @var resource */
	private $tmpFile;

	/**
	 * @param Image $image
	 * @param string $id
	 */
	public function __construct(Image $image, string $id) {
		/** @var resource|false $tmp */
		$tmp = tmpfile();
		if ($tmp === false) {
			throw new RuntimeException('Cannot create tmp file.');
		}
		$this->tmpFile = $tmp;
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
