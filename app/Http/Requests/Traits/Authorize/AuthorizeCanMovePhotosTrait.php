<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization of `Photo::copy`, also the
 * photo part of `Photo::move`.
 *
 * Target: edit. Each photo: move grant, and — when it lands in an album of
 * another owner than the photo's — full-photo access and download, because
 * owning that album would otherwise confer both.
 */
trait AuthorizeCanMovePhotosTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->canMovePhotosInto($this->album);
	}

	protected function canMovePhotosInto(?Album $target): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $target])) {
			return false;
		}

		// Aggregate checks: a fixed number of queries, whatever the batch size.
		$photo_ids = $this->photos->map(fn (Photo $photo): string => $photo->id)->all();
		if (!Gate::check(PhotoPolicy::CAN_MOVE_ID, [Photo::class, $photo_ids])) {
			return false;
		}

		$crossing_ids = $this->photosLeavingTheirOwner($target);

		return $crossing_ids === [] || Gate::check(PhotoPolicy::CAN_ACCESS_FULL_AND_DOWNLOAD_ID, [Photo::class, $crossing_ids]);
	}

	/**
	 * Photos that would land in an album of another owner than theirs. Photos
	 * staying within their owner's albums (or going to their unsorted) are fine.
	 *
	 * @return string[]
	 */
	protected function photosLeavingTheirOwner(?Album $target): array
	{
		if ($target === null) {
			return [];
		}

		return $this->photos
			->filter(fn (Photo $photo): bool => $photo->owner_id !== $target->owner_id)
			->map(fn (Photo $photo): string => $photo->id)
			->values()
			->all();
	}
}
