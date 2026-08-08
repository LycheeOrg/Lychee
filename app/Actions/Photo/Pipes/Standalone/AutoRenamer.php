<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;
use App\Metadata\Renamer\PhotoRenamer;
use Illuminate\Support\Facades\Log;

/**
 * Apply renaming rules to the photo title.
 *
 * Maybe later we can extend the renamer to also consider the photo metadata such as exif to apply more complex renaming rules.
 * For now it only applies the renaming rules defined by the user.
 *
 * Maybe also consider whether Renaming should be applied at upload time.
 */
class AutoRenamer extends AbstractStandalonePipe
{
	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Skip if the caller explicitly provided a title at upload time (FR-041-06).
		// User-supplied titles take precedence and must not be overwritten by renamer rules.
		if ($state->title !== null) {
			Log::info('Photo has a title, we don\'t rename it, skipping');

			return $next($state);
		}

		// Skip if not enabled.
		if (!$state->shall_rename_photo_title) {
			Log::info('renaming not necessary, skipping');

			return $next($state);
		}

		$renamer = new PhotoRenamer(
			user_id: $state->intended_owner_id
		);
		$state->photo->title = $renamer->handle($state->photo->title);

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.auto_renamer';
	}
}
