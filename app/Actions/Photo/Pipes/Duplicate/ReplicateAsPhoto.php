<?php

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class ReplicateAsPhoto implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->duplicate = $state->photo;
		$state->photo = $state->duplicate->replicate();

		return $next($state);
	}
}
