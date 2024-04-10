<?php

namespace App\Actions\Photo\Pipes\Shared;

use App\Actions\User\Notify;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;

/**
 * Notify by email if a picture has been added.
 */
class NotifyAlbums implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		if ($state->getPhoto()->album_id !== null) {
			$notify = new Notify();
			$notify->do($state->getPhoto());
		}

		return $next($state);
	}
}