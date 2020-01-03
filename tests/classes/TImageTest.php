<?php declare(strict_types = 1);

namespace Test;

use Nette\Http\FileUpload;
use WebChemistry\Images\Resources\Transfer\StringResource;

trait TImageTest {

	private function createUploadResource(string $name = 'upload.gif') {
		if (!file_exists(UPLOAD_GIF)) {
			copy(IMAGE_GIF, UPLOAD_GIF);
		}
		$upload = new FileUpload([
			'name' => $name,
			'tmp_name' => UPLOAD_GIF,
			'type' => 'image/gif',
			'error' => 0,
			'size' => 1,
		]);

		return $this->storage->createUploadResource($upload);
	}

	private function createStringResource() {
		if (!file_exists(UPLOAD_GIF)) {
			copy(IMAGE_GIF, UPLOAD_GIF);
		}
		return new StringResource(file_get_contents(UPLOAD_GIF), 'string.gif');
	}

	private function sameOriginal(string $path, string $original = 'image.gif') {
		return md5_file(IMAGE_DIR . '/' . $original) === md5_file($path);
	}

	private function createImageResource(string $id = 'upload.gif', string $imageName = 'image.gif') {
		$resource = $this->storage->createLocalResource(IMAGE_DIR . '/' . $imageName);
		$resource->setId($id);

		return $resource;
	}

}
