<?php

declare(strict_types=1);

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Basic definition of a Standalone Photo pipe.
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