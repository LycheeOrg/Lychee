<?php

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class SetLivePartnerAsPhoto implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->photo = $state->livePartner;

		return $next($state);
	}
}
