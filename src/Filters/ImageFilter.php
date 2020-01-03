<?php declare(strict_types = 1);

namespace WebChemistry\Images\Filters;

use Nette\SmartObject;
use WebChemistry\Images\Image\IImage;
use WebChemistry\Images\Resources\IResource;

final class ImageFilter implements IImageFilter {

	use SmartObject;

	/** @var IFilterRegistry */
	private $filterRegistry;

	public function __construct(IFilterRegistry $filterRegistry) {
		$this->filterRegistry = $filterRegistry;
	}

	public function filter(IResource $resource, ?IImage $image = null): void {
		$args = new FilterArgs($image);
		foreach ($resource->getFilters() as $filter) {
			$this->filterRegistry->call($args, $filter->getName(), $filter->getArguments());
		}
	}

}
