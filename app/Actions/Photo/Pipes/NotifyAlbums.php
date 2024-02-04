<?php

namespace App\Actions\Photo\Pipes;

use App\Actions\User\Notify;
use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

/**
 * Notify by email if a picture has been added.
 */
class NotifyAlbums implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if ($state->photo->album_id !== null) {
			$notify = new Notify();
			$notify->do($state->photo);
		}

		return $next($state);
	}
}