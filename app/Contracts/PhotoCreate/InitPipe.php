<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\InitDTO;

/**
 * Initial pipes, could be seen as pre-processing steps.
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