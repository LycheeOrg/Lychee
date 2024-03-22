<?php

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class ResetFile implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->videoFile = $state->oldVideo->size_variants->getOriginal()->getFile();

		return $next($state);
	}
}
