<?php

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;

class SetStarred implements SharedPipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		$state->photo->is_starred = $state->is_starred;

		return $next($state);
	}
}