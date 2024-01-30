<?php

namespace App\Actions\Photo\Pipes;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Models\Album;

class SetParentAndOwnership implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if ($state->album instanceof Album) {
			$state->photo->album_id = $state->album->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', $state->album);
			$state->photo->owner_id = $state->album->owner_id;
		} else {
			$state->photo->album_id = null;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', null);
			$state->photo->owner_id = $state->intendedOwnerId;
		}

		return $next($state);
	}
}

