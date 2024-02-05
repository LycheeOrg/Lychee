<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Models\Photo;

class CreatePhoto implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		$state->photo = new Photo();

		return $next($state);
	}
}
