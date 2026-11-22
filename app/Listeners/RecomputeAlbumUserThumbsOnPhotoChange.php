<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Actions\Album\AlbumUserThumbTargets;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
use App\Jobs\RecomputeAlbumUserThumbsJob;

/**
 * Keeps `album_user_thumbs` warm whenever a photo change could affect the
 * cached thumb of a smart, tag, or person album.
 *
 * The affected albums are resolved by {@link AlbumUserThumbTargets}, also
 * used by the photo delete action. For every relevant photo event, two
 * lookups are dispatched:
 * - Forward: albums whose tag/person set currently intersects this photo -
 *   this photo may now be a new (or re-ranked) candidate for their thumb.
 * - Backward: albums that currently cache this exact photo as their thumb -
 *   whatever changed about it may mean it no longer qualifies (or ranks
 *   lower), so a replacement must be picked.
 * Smart albums have no reverse tag/person index, so all cached smart album
 * rows are refreshed unconditionally; each is a cheap single-row lookup.
 *
 * Photo deletion is not handled here: the photo delete action resolves the
 * targets once per request and dispatches the jobs after the delete.
 */
class RecomputeAlbumUserThumbsOnPhotoChange
{
	public function __construct(
		private AlbumUserThumbTargets $targets,
	) {
	}

	public function handlePhotoSaved(PhotoSaved $event): void
	{
		$this->refreshForPhotoIds($event->photo_ids);
	}

	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->refreshForPhotoIds($event->photo_ids);
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
		$this->targets->dispatch($this->targets->resolve($photo_ids));
	}
}
