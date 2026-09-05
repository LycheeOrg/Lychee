<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Events\PhotoAdded;
use App\Events\PhotoSaved;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Album;
use App\Services\PhotoBucketComputer;
use Illuminate\Support\Facades\DB;

/**
 * This MUST be called after a first save() otherwise we do not have a photo id.
 */
class SetParent implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		if ($state->album instanceof Album) {
			if ($state->photo->id === null) {
				throw new LycheeLogicException('Photo Id is null, cannot set a parent album.');
			}

			// Avoid duplicates key constraint
			DB::table(PA::PHOTO_ALBUM)
				->where(PA::PHOTO_ID, '=', $state->photo->id)
				->where(PA::ALBUM_ID, '=', $state->album->id)
				->delete();

			// Compute this new pivot row's bucket_id inline, against
			// $state->album's own currently effective photo-sort/timeline
			// settings.
			$bucket_computer = resolve(PhotoBucketComputer::class);
			$sorting = $state->album->getEffectivePhotoSorting();
			$granularity = $bucket_computer->resolveGranularity($state->album->photo_timeline);
			$photo = $state->photo;

			// Insert the new link
			DB::table(PA::PHOTO_ALBUM)
				->insert([
					'photo_id' => $photo->id,
					'album_id' => $state->album->id,
					'bucket_id' => $bucket_computer->compute(
						sorting_column: $sorting->column,
						granularity: $granularity,
						title: $photo->title,
						title_base: $photo->title_base ?? '',
						created_at: $photo->created_at,
						taken_at: $photo->taken_at,
						is_highlighted: $photo->is_highlighted,
						type: $photo->type ?? '',
						rating_avg: $photo->rating_avg,
					),
				]);

			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->load('albums');

			// Dispatch event for album stats recomputation
			// This must be done after SetParent so the photo_album relationship exists
			PhotoSaved::dispatch([$state->photo->id]);

			// Dispatch PhotoAdded for new photo records only (upload, import, duplication).
			// Existing records that were re-saved into a different album are handled by PhotoMoved.
			if ($state->photo->wasRecentlyCreated) {
				PhotoAdded::dispatch($state->photo->id);
			}
		}

		return $next($state);
	}
}