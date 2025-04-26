<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;

class InitNamingStrategy implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		$state->naming_strategy = resolve(AbstractSizeVariantNamingStrategy::class);
		$state->naming_strategy->setPhoto($state->photo);
		$state->naming_strategy->setExtension(
			$state->source_file->getOriginalExtension()
		);

		return $next($state);
	}
}
