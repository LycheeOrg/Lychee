<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;
use App\Repositories\ConfigManager;

class ReplaceOriginalWithBackup extends AbstractStandalonePipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->backup_file === null) {
			return $next($state);
		}

		if ($this->config_manager->getValueAsBool('keep_original_untouched')) {
			$state->target_file->write($state->backup_file->read());
			$state->target_file->close();
		}

		$state->backup_file->delete();

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.replace_original_with_backup';
	}
}
