<?php declare(strict_types = 1);

namespace Test;

use Nette\SmartObject;
use WebChemistry\Images\Resolvers\IDefaultImageResolver;
use WebChemistry\Images\Utils\ISafeLink;
use WebChemistry\Images\Utils\ISafeLinkFactory;
use WebChemistry\Images\Utils\SafeLink;

final class SafeLinkFactory implements ISafeLinkFactory {

	use SmartObject;

	/** @var IDefaultImageResolver */
	private $defaultImageResolver;

	public function __construct(IDefaultImageResolver $defaultImageResolver) {
		$this->defaultImageResolver = $defaultImageResolver;
	}

	public function create(callable $linkGetter): ISafeLink {
		return new SafeLink(false, $linkGetter, $this->defaultImageResolver);
	}

}
