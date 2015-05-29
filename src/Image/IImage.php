<?php

namespace WebChemistry\Images\Image;

interface IImage {

	public function getFlag();

	public function setFlag($flag);

	public function getName();

	public function getNamespace();

	public function getWidth();

	public function setWidth($width);

	public function getHeight();

	public function setHeight($height);

	public function setSize($size);

	public function isBaseUri();

	public function setAbsoluteName($name);

	public function setName($name);
}
