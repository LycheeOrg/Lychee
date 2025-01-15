<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use Illuminate\Support\Collection;

class TopAlbumDTO
{
	/**
	 * Sorting criterion.
	 *
	 * @param Collection<int,\App\SmartAlbums\BaseSmartAlbum> $smart_albums
	 * @param Collection<int,\App\Models\TagAlbum>            $tag_albums
	 * @param Collection<int,\App\Models\Album>               $albums
	 * @param Collection<int,\App\Models\Album>|null          $shared_albums
	 */
	public function __construct(
		public Collection $smart_albums,
		public Collection $tag_albums,
		public Collection $albums,
		public ?Collection $shared_albums = null,
	) {
	}
}
