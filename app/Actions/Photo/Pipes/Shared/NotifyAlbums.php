<?php

namespace App\Actions\Photo\Pipes\Shared;

use App\Actions\User\Notify;
use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;

/**
 * Notify by email if a picture has been added.
 */
class NotifyAlbums implements SharedPipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		if ($state->photo->album_id !== null) {
			$notify = new Notify();
			$notify->do($state->photo);
		}

		return $next($state);
	}
}