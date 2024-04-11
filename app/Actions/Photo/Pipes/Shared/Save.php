<?php

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;

class Save implements SharedPipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		$state->photo->save();

		return $next($state);
	}
}