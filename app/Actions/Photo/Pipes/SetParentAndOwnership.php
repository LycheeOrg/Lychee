<?php

namespace App\Actions\Photo\Pipes;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetParentAndOwnership implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if ($state->parameters->album !== null) {
			$state->photo->album_id = $state->parameters->album->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', $state->parameters->album);
			$state->photo->owner_id = $state->parameters->album->owner_id;
		} else {
			$state->photo->album_id = null;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', null);
			$state->photo->owner_id = $state->parameters->intendedOwnerId;
		}

		return $next($state);
	}
}

