<?php

namespace App\Contracts;

use App\DTO\PhotoCreateDTO;

/**
 * Basic definition of a Photo creation pipe.
 *
 * This allows to clarify which steps are applied in which order.
 */
interface PhotoCreatePipe
{
	/**
	 * @param PhotoCreateDTO                                  $state
	 * @param \Closure(PhotoCreateDTO $state): PhotoCreateDTO $next
	 *
	 * @return PhotoCreateDTO
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO;
}