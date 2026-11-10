<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Services\ModerationHistory;

/**
 * Records a `held` / `upload` moderation event when the new photo is hidden
 * (Feature 089, FR-089-02).
 *
 * Last pipe of the standalone pipelines: the photo row exists and
 * `is_validated` is final (trust level `check`, or hide-on-scan).
 */
class RecordUploadHold extends AbstractStandalonePipe
{
	public function __construct(
		protected readonly ModerationHistory $moderation_history,
	) {
	}

	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->photo->is_validated === false) {
			$this->moderation_history->record([$state->photo->id], ModerationEvent::HELD, ModerationSource::UPLOAD);
		}

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.record_upload_hold';
	}
}
