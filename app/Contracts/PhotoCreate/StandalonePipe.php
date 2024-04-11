<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Basic definition of a Standalone Photo pipe.
 *
 * This allows to clarify which steps are applied in which order.
 */
interface StandalonePipe
{
	/**
	 * @param StandaloneDTO                                 $state
	 * @param \Closure(StandaloneDTO $state): StandaloneDTO $next
	 *
	 * @return StandaloneDTO
	 */
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO;
}