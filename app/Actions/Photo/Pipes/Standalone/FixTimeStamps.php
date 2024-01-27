<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class FixTimeStamps implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		$state->photo->updateTimestamps();

		return $next($state);
	}
}
