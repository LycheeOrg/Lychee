<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Constants\PersonAlbumPersons as PAP;
use App\Enum\SmartAlbumType;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
use App\Events\PhotoWillBeDeleted;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Models\AlbumUserThumb;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\DB;

/**
 * Keeps `album_user_thumbs` warm whenever a photo change could affect the
 * cached thumb of a smart, tag, or person album.
 *
 * For every relevant photo event, two lookups are dispatched:
 * - Forward: albums whose tag/person set currently intersects this photo -
 *   this photo may now be a new (or re-ranked) candidate for their thumb.
 * - Backward: albums that currently cache this exact photo as their thumb -
 *   whatever changed about it may mean it no longer qualifies (or ranks
 *   lower), so a replacement must be picked.
 * Smart albums have no reverse tag/person index, so all cached smart album
 * rows are refreshed unconditionally; each is a cheap single-row lookup.
 */
class RecomputeAlbumUserThumbsOnPhotoChange
{
	public function handlePhotoSaved(PhotoSaved $event): void
	{
		$this->refreshForPhotoId($event->photo_id);
	}

	public function handlePhotoWillBeDeleted(PhotoWillBeDeleted $event): void
	{
		$this->refreshForPhotoId($event->photo_id);
	}

	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->refreshForPhotoId($event->photo_id);
	}

	public function handlePhotoHighlightToggled(PhotoHighlightToggled $event): void
	{
		$this->refreshForPhotoIds($event->photo_ids);
	}

	public function handlePhotoRatingChanged(PhotoRatingChanged $event): void
	{
		$this->refreshForPhotoId($event->photo_id);
	}

	public function handlePhotoTagsChanged(PhotoTagsChanged $event): void
	{
		$this->refreshForPhotoIds($event->photo_ids);
	}

	public function handlePhotoPersonsChanged(PhotoPersonsChanged $event): void
	{
		$this->refreshForPhotoIds($event->photo_ids);
	}

	private function refreshForPhotoId(string $photo_id): void
	{
		$this->refreshForPhotoIds([$photo_id]);
	}

	/**
	 * Batched so a multi-photo change (e.g. bulk highlight toggle) resolves
	 * the affected album set once across the whole batch, instead of
	 * dispatching a full set of {@link RecomputeAlbumUserThumbsJob}s per photo.
	 *
	 * @param array<int,string> $photo_ids
	 */
	private function refreshForPhotoIds(array $photo_ids): void
	{
		if ($photo_ids === []) {
			return;
		}

		foreach (SmartAlbumType::cases() as $smart_album_type) {
			RecomputeAlbumUserThumbsJob::dispatch(RecomputeAlbumUserThumbsJob::KIND_SMART, $smart_album_type->value);
		}

		$tag_ids = DB::table('photos_tags')->whereIn('photo_id', $photo_ids)->distinct()->pluck('tag_id');
		if ($tag_ids->isNotEmpty()) {
			$tag_album_ids = DB::table('tag_albums_tags')->whereIn('tag_id', $tag_ids)->distinct()->pluck('album_id');
			foreach ($tag_album_ids as $tag_album_id) {
				RecomputeAlbumUserThumbsJob::dispatch(RecomputeAlbumUserThumbsJob::KIND_TAG, $tag_album_id);
			}
		}

		$person_ids = DB::table('faces')
			->whereIn('photo_id', $photo_ids)
			->whereNotNull('person_id')
			->where('is_dismissed', '=', false)
			->distinct()
			->pluck('person_id');
		if ($person_ids->isNotEmpty()) {
			$person_album_ids = DB::table(PAP::PERSON_ALBUM_PERSONS)->whereIn(PAP::PERSON_ID, $person_ids)->distinct()->pluck('album_id');
			foreach ($person_album_ids as $person_album_id) {
				RecomputeAlbumUserThumbsJob::dispatch(RecomputeAlbumUserThumbsJob::KIND_PERSON, $person_album_id);
			}
		}

		// Backward lookup: albums which currently cache any of these photos as
		// their thumb, regardless of whether they still match their tag/person
		// set today (e.g. the last matching tag was just removed).
		$cached_album_ids = AlbumUserThumb::query()->whereIn('photo_id', $photo_ids)->distinct()->pluck('album_id');
		foreach ($cached_album_ids as $album_id) {
			if (SmartAlbumType::tryFrom($album_id) !== null) {
				// Already covered by the unconditional smart album refresh above.
				continue;
			}
			if (TagAlbum::find($album_id) !== null) {
				RecomputeAlbumUserThumbsJob::dispatch(RecomputeAlbumUserThumbsJob::KIND_TAG, $album_id);
			} elseif (PersonAlbum::find($album_id) !== null) {
				RecomputeAlbumUserThumbsJob::dispatch(RecomputeAlbumUserThumbsJob::KIND_PERSON, $album_id);
			}
		}
	}
}
