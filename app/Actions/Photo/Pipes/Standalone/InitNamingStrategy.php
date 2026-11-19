<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\DTO\PhotoCreate\StandaloneDTO;
use Illuminate\Support\Facades\Log;

class InitNamingStrategy extends AbstractStandalonePipe
{
	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		Log::debug('Executing InitNamingStrategy pipe');
		$state->naming_strategy = resolve(AbstractSizeVariantNamingStrategy::class);
		$state->naming_strategy->setPhoto($state->photo);
		$state->naming_strategy->setExtension(
			$state->source_file->getOriginalExtension()
		);

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.init_naming_strategy';
	}
}
