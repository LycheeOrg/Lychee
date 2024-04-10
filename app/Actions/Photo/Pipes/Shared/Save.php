<?php

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;

class Save
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		$state->getPhoto()->save();

		return $next($state);
	}
}