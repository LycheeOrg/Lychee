<?php

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetDuplicateAsPhoto implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->photo = $state->duplicate;

		return $next($state);
	}
}
