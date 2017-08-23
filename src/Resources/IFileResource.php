<?php

namespace WebChemistry\Images\Resources;

interface IFileResource extends IResource {

	/**
	 * @return IFileResource
	 */
	public function getOriginal();

}
