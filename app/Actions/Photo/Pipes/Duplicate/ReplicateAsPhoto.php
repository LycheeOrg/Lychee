<?php

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreate\DuplicatePipe;
use App\DTO\PhotoCreate\DuplicateDTO;

class ReplicateAsPhoto implements DuplicatePipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		$state->replicatePhoto();

		return $next($state);
	}
}
