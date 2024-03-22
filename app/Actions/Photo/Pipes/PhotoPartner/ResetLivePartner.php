<?php

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class ResetLivePartner implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->livePartner = $state->photo;

		return $next($state);
	}
}
