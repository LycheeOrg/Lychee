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
 * Authorization of `Photo::copy` (Feature 072, FR-072-10/12/17), also the
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

		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_MOVE, [Photo::class, $photo])) {
				return false;
			}
			if (!$this->mayPlacePhotoIn($photo, $target)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * True when $photo stays within its owner's albums (or goes to their
	 * unsorted), or when the user already holds full-photo access and download.
	 */
	protected function mayPlacePhotoIn(Photo $photo, ?Album $target): bool
	{
		if ($target === null || $target->owner_id === $photo->owner_id) {
			return true;
		}

		return Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]) &&
			Gate::check(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $photo]);
	}
}
