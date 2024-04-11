<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\DuplicateDTO;

/**
 * Basic definition of a Photo shared pipe.
 *
 * This pipes makes use of union types (|) to support the different DTO.
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