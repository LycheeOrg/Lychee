<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when one or more albums' own *photo*-sort settings
 * (`sorting_col`/`sorting_order`/`photo_timeline`) change — the dedicated
 * cache-invalidation signal for the same trigger that dispatches
 * {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob}, mirroring how
 * {@see AlbumChildrenChanged} exists alongside
 * {@see \App\Jobs\RecomputeChildAlbumBucketsJob} for the album-bucket case.
 */
class AlbumPhotoSortingChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * @param array<int,string> $album_ids IDs of the albums whose own photo-sort settings changed, batched so listeners can act once per call
	 */
	public function __construct(public array $album_ids)
	{
	}
}
