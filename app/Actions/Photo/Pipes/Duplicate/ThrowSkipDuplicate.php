<?php

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;

class ThrowSkipDuplicate implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if (!$state->parameters->importMode->shallSkipDuplicates()) {
			return $next($state);
		}

		if ($state->hasBeenReSynced) {
			throw new PhotoResyncedException();
		}
		throw new PhotoSkippedException();
	}
}
