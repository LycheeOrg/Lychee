<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\DuplicateDTO;

/**
 * Basic definition of a Photo creation pipe.
 *
 * This allows to clarify which steps are applied in which order.
 */
interface SharedPipe
{
	/**
	 * @param DuplicateDTO                                $state
	 * @param \Closure(DuplicateDTO $state): DuplicateDTO $next
	 *
	 * @return DuplicateDTO
	 */
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO;
}