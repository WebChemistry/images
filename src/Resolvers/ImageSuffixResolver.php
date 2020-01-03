<?php declare(strict_types = 1);

namespace WebChemistry\Images\Resolvers;

use LogicException;
use WebChemistry\Images\Resources\Transfer\ITransferResource;

final class ImageSuffixResolver implements IImageSuffixResolver {

	private const SUPPORTED = ['jpg' => true, 'png' => true, 'gif' => true, 'webp' => true];
	private const MIME_TYPE_TO_SUFFIX = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/gif' => 'gif',
		'image/webp' => 'webp',
	];

	public function resolve(ITransferResource $resource): void {
		if ($resource->getSuffix() !== null && isset(self::SUPPORTED[$resource->getSuffix()])) {
			return;
		}

		$mimeType = $resource->getMimeType()->toString();
		if (!isset(self::MIME_TYPE_TO_SUFFIX[$mimeType])) {
			throw new LogicException(sprintf('Mime type %s is not supported', $mimeType));
		}

		$resource->setSuffix(self::MIME_TYPE_TO_SUFFIX[$mimeType]);
	}

}
