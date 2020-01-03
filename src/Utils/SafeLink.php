<?php declare(strict_types = 1);

namespace WebChemistry\Images\Utils;

use Nette\SmartObject;
use Throwable;
use Tracy\ILogger;
use WebChemistry\Images\Resolvers\IDefaultImageResolver;
use WebChemistry\Images\Resources\IFileResource;

final class SafeLink implements ISafeLink {

	use SmartObject;

	/** @var bool */
	private $safeLink;

	/** @var callable */
	private $linkGetter;

	/** @var IDefaultImageResolver */
	private $defaultImageResolver;

	/** @var ILogger|null */
	private $logger;

	public function __construct(bool $safeLink, callable $linkGetter, IDefaultImageResolver $defaultImageResolver, ?ILogger $logger = null) {
		$this->safeLink = $safeLink;
		$this->linkGetter = $linkGetter;
		$this->defaultImageResolver = $defaultImageResolver;
		$this->logger = $logger;
	}

	public function call(?IFileResource $resource, array $options): ?string {
		try {
			$location = null;
			if ($resource && !$resource->isEmpty()) {
				$location = ($this->linkGetter)($resource);
			}

			if ($location === null) {
				$location = $this->getDefaultImage($resource, $options['default'] ?? null);
			}
		} catch (Throwable $exception) {
			if (!$this->safeLink) {
				throw $exception;
			}

			if ($this->logger) {
				$this->logger->log($exception);
			}

			// try default image
			try {
				$location = $this->getDefaultImage($resource, $options['default'] ?? null);
			} catch (Throwable $exception) {
				$location = null;

				if ($this->logger) {
					$this->logger->log($exception);
				}
			}
		}

		return $location;
	}

	protected function getDefaultImage(?IFileResource $resource, ?string $default): ?string {
		$resource = $this->defaultImageResolver->resolve($resource, $default);
		if (!$resource) {
			return null;
		}

		return ($this->linkGetter)($resource);
	}

}
