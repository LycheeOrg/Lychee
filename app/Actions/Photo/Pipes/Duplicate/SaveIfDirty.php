<?php

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use Illuminate\Support\Facades\Log;

class SaveIfDirty implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		if ($state->photo->isDirty()) {
			Log::notice(__METHOD__ . ':' . __LINE__ . ' Updating metadata of existing photo.');
			$state->photo->save();
			$state->hasBeenReSynced = true;
		}

		return $next($state);
	}
}
