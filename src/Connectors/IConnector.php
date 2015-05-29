<?php

namespace WebChemistry\Images\Connectors;

use Nette\Http\FileUpload;
use WebChemistry\Images\Bridges\Nette\Image;
use WebChemistry\Images\Image\Content;
use WebChemistry\Images\Image\Info;
use WebChemistry\Images\Image\Upload;

interface IConnector {

	/**
	 * @param FileUpload $upload
	 * @param string     $namespace
	 * @return Upload
	 */
	public function processUpload(FileUpload $upload, $namespace = NULL);

	/**
	 * @param      $content
	 * @param      $name
	 * @param null $namespace
	 * @return Content
	 */
	public function processContent($content, $name, $namespace = NULL);

	/**
	 * @param array  $helpers
	 * @param string $absoluteName
	 * @param null   $size
	 * @param null   $flag
	 * @param null   $noImage
	 * @return \WebChemistry\Images\Image\Image
	 */
	public function processGet(array $helpers, $absoluteName, $size = NULL, $flag = NULL, $noImage = NULL);

	/**
	 * @param string $absoluteName
	 * @return mixed
	 */
	public function processDelete($absoluteName);

	/**
	 * @param array $settings
	 */
	public function setSettings(array $settings);

	/**
	 * @param Info $info
	 * @return array
	 */
	public function getImageSize(Info $info);

	/**
	 * @param Info $info
	 * @return string
	 */
	public function getUniqueImageName(Info $info);

	/**
	 * @param Image   $image
	 * @param Info    $info
	 * @param integer $imageType
	 */
	public function save(Image $image, Info $info, $imageType);

	/**
	 * @param Info $info
	 */
	public function delete(Info $info);

	/**
	 * @param Info $info
	 * @return Info
	 */
	public function getLink(Info $info);

	/**
	 * @param Info $info
	 * @return Image
	 */
	public function getNetteImage(Info $info);

	/**
	 * @param Info $info
	 * @return bool
	 */
	public function isExists(Info $info);
}