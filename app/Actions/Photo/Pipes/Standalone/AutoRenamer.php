<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Metadata\Renamer\PhotoRenamer;
use App\Repositories\ConfigManager;
use LycheeVerify\Contract\VerifyInterface;

/**
 * Apply renaming rules to the photo title.
 *
 * Maybe later we can extend the renamer to also consider the photo metadata such as exif to apply more complex renaming rules.
 * For now it only applies the renaming rules defined by the user.
 *
 * Maybe also consider whether Renaming should be applied at upload time.
 */
class AutoRenamer implements StandalonePipe
{
	public function __construct(
		protected readonly VerifyInterface $verify,
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Skip if not enabled.
		if (!$state->shall_rename_photo_title) {
			return $next($state);
		}

		$renamer = new PhotoRenamer(
			verify: $this->verify,
			config_manager: $this->config_manager,
			user_id: $state->intended_owner_id
		);
		$state->photo->title = $renamer->handle($state->photo->title);

		return $next($state);
	}
}