<?php declare(strict_types = 1);

namespace WebChemistry\Images\Batches;

use WebChemistry\Images\IUnitOfWork;

interface IBatch extends IUnitOfWork {

	public function flush(): void;

}
