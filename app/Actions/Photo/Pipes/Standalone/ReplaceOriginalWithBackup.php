<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Models\Configs;

class ReplaceOriginalWithBackup implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->backupFile === null) {
			return $next($state);
		}

		if (Configs::getValueAsBool('keep_original_untouched')) {
			$state->targetFile->write($state->backupFile->read());
			$state->targetFile->close();
		}

		$state->backupFile->delete();

		return $next($state);
	}
}
