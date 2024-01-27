<?php

namespace App\Actions\Photo\Pipes;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetStarred implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		$state->photo->is_starred = $state->parameters->is_starred;

		return $next($state);
	}
}
