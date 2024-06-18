<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;

/**
 * Persist current Photo object into database.
 */
class Save implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		$state->getPhoto()->save();

		return $next($state);
	}
}