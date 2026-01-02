<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\Log;

/**
 * Listener that triggers album size statistics recomputation when size variants are mutated.
 *
 * Listens to Eloquent model events on SizeVariant:
 * - saved: Covers variant creation and filesize updates (e.g., watermarking, regeneration)
 * - deleted: Covers variant deletion
 *
 * For each variant mutation, dispatches RecomputeAlbumSizeJob for all albums containing the photo.
 */
class RecomputeAlbumSizeOnVariantMutation
{
	/**
	 * Handle SizeVariant saved event (creation, update).
	 *
	 * When a size variant is saved, recompute sizes for all albums containing the variant's photo.
	 * This covers:
	 * - Variant creation (photo upload)
	 * - Variant regeneration (size variant regeneration command)
	 * - Variant filesize changes (watermarking)
	 *
	 * @param SizeVariant $variant
	 *
	 * @return void
	 */
	public function handleVariantSaved(SizeVariant $variant): void
	{
		// Get the photo this variant belongs to
		$photo = $variant->photo;
		if ($photo === null) {
			Log::warning("SizeVariant {$variant->id} has no associated photo, skipping size recompute");

			return;
		}

		// Get all albums this photo belongs to
		$album_ids = $photo->albums()->pluck('id');

		foreach ($album_ids as $album_id) {
			Log::debug("SizeVariant {$variant->id} (type {$variant->type->value}) for photo {$photo->id} saved, dispatching size recompute for album {$album_id}");
			RecomputeAlbumSizeJob::dispatch($album_id);
		}
	}

	/**
	 * Handle SizeVariant deleted event.
	 *
	 * When a size variant is deleted, recompute sizes for all albums containing the variant's photo.
	 *
	 * @param SizeVariant $variant
	 *
	 * @return void
	 */
	public function handleVariantDeleted(SizeVariant $variant): void
	{
		// Get the photo this variant belongs to
		// Note: The relationship should still be available during the deleting event
		$photo = $variant->photo;
		if ($photo === null) {
			Log::warning("SizeVariant {$variant->id} has no associated photo, skipping size recompute");

			return;
		}

		// Get all albums this photo belongs to
		$album_ids = $photo->albums()->pluck('id');

		foreach ($album_ids as $album_id) {
			Log::debug("SizeVariant {$variant->id} (type {$variant->type->value}) for photo {$photo->id} deleted, dispatching size recompute for album {$album_id}");
			RecomputeAlbumSizeJob::dispatch($album_id);
		}
	}
}
