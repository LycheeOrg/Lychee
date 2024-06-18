<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Set the timestamps of the creation and updated_at time.
 */
class FixTimeStamps implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		$state->photo->updateTimestamps();

		return $next($state);
	}
}
