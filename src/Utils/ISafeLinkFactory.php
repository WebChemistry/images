<?php declare(strict_types = 1);

namespace WebChemistry\Images\Utils;

interface ISafeLinkFactory {

	public function create(callable $linkGetter): ISafeLink;

}