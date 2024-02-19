<?php

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreatePipe;
use App\DTO\ImportMode;
use App\DTO\PhotoCreateDTO;

class ResetParameters implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->importMode = new ImportMode(deleteImported: true);

		return $next($state);
	}
}
