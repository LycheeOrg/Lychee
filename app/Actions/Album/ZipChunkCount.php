<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Events\Metrics\AlbumDownload;
use App\Http\Resources\GalleryConfigs\ZipChunkData;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ZipChunkCount
{
	public function __construct(
		private bool $should_measure,
		private string $visitor_id,
	) {
	}

	/**
	 * @param Collection<int,AbstractAlbum> $albums
	 *
	 * @return ZipChunkData
	 */
	public function getZipChunkData(
		Collection $albums,
	): ZipChunkData {
		$chunk_size = max(1, request()->configs()->getValueAsInt('download_archive_chunk_size'));

		// We dispatch one event per album.
		$total = 0;
		foreach ($albums as $album) {
			$total += $this->getPhotoCountForAlbum($album);
		}
		$total_chunks = max(1, (int) ceil($total / $chunk_size));

		return new ZipChunkData(
			total_chunks: $total_chunks,
			total_photos: $total,
		);
	}

	/**
	 * Count recursively.
	 * Not ideal for query, but we need to check permissions and dispatch events, so we need to load the albums anyway.
	 *
	 * @param AbstractAlbum $album
	 *
	 * @return int
	 */
	private function getPhotoCountForAlbum(
		AbstractAlbum $album,
	): int {
		if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, $album)) {
			return 0;
		}

		AlbumDownload::dispatchIf($this->should_measure, $this->visitor_id, $album->get_id());
		$total = $album->photos()->count();

		if ($album instanceof Album) {
			foreach ($album->children()->get() as $child) { /** @phpstan-ignore foreach.nonIterable (false positive) */
				$total += $this->getPhotoCountForAlbum($child);
			}
		}

		return $total;
	}
}