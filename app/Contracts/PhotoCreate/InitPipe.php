<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\InitDTO;

/**
 * Basic definition of a Photo creation pipe.
 *
 * This allows to clarify which steps are applied in which order.
 */
interface InitPipe
{
	/**
	 * @param InitDTO                           $state
	 * @param \Closure(InitDTO $state): InitDTO $next
	 *
	 * @return InitDTO
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO;
}