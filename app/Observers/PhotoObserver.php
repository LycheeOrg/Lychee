<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Observers;

use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Models\Photo;

/**
 * Eloquent observer for the Photo model.
 *
 * Dispatches a job to purge face embeddings from the AI Vision service
 * whenever a photo is hard-deleted, so that stale vectors do not affect
 * future clustering or selfie-match results.
 */
class PhotoObserver
{
	/**
	 * Handle the Photo "deleting" event.
	 * Called before the record (and cascade-deleted faces) are removed.
	 */
	public function deleting(Photo $photo): void
	{
		$face_ids = $photo->faces()->pluck('id')->all();
		if ($face_ids !== []) {
			DeleteFaceEmbeddingsJob::dispatch($face_ids);
		}
	}
}
