<?php

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\ImportMode;
use App\DTO\PhotoCreateDTO;

// Now we re-use the same strategy as if the freshly created photo
// entity had been uploaded first and as if the already existing video
// had been uploaded after that.
// We use the original size variant of the video as the "source file"
// We request that the "imported" file shall be deleted, this actually
// "steals away" the stored video file from the existing video entity
// and moves it to the correct destination of a live partner for the
// photo.
class ResetParameters implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->importMode = new ImportMode(deleteImported: true);

		return $next($state);
	}
}
