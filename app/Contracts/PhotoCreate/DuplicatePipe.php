<?php

declare(strict_types=1);

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\DuplicateDTO;

/**
 * Basic definition of a Duplicate Photo pipe.
 */
interface DuplicatePipe
{
	/**
	 * @param DuplicateDTO                                $state
	 * @param \Closure(DuplicateDTO $state): DuplicateDTO $next
	 *
	 * @return DuplicateDTO
	 */
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO;
}