<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\Http\Resources\Insights\TimeSpanData;
use App\Models\Photo;

/**
 * Fills the thumbnail URLs of the time-span photos (Feature 085, FR-085-09).
 *
 * Runs on every request, after the cache: URLs may be signed and expire.
 */
class ResolveInsightsThumbs
{
	public function apply(TimeSpanData $time_span): void
	{
		$ids = array_values(array_filter([
			$time_span->first?->photo_id,
			$time_span->last?->photo_id,
			$time_span->busiest_day?->photo_id,
		], fn (?string $id) => $id !== null));

		if ($ids === []) {
			return;
		}

		$urls = Photo::query()
			->with('size_variants')
			->whereIn('id', $ids)
			->get()
			->mapWithKeys(fn (Photo $photo) => [$photo->id => $photo->size_variants->getThumb()?->url])
			->all();

		if ($time_span->first !== null) {
			$time_span->first->thumb_url = $urls[$time_span->first->photo_id] ?? null;
		}
		if ($time_span->last !== null) {
			$time_span->last->thumb_url = $urls[$time_span->last->photo_id] ?? null;
		}
		if ($time_span->busiest_day !== null) {
			$time_span->busiest_day->thumb_url = $urls[$time_span->busiest_day->photo_id] ?? null;
		}
	}
}
