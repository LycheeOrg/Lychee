<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Album\BaseArchive as AlbumBaseArchive;
use App\Actions\Photo\BaseArchive as PhotoBaseArchive;
use App\Events\Metrics\AlbumDownload;
use App\Events\Metrics\PhotoDownload;
use App\Http\Controllers\MetricsController;
use App\Http\Requests\Album\ZipChunksRequest;
use App\Http\Requests\Album\ZipRequest;
use App\Http\Requests\Traits\HasVisitorIdTrait;
use App\Http\Resources\GalleryConfigs\ZipChunkData;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipController
{
	use HasVisitorIdTrait;

	/**
	 * Return the archive of the pictures of the album and its sub-albums.
	 */
	public function getArchive(ZipRequest $request): StreamedResponse
	{
		// We only measure the download if the chunk slice is null, i.e. if we are downloading the whole archive
		// in one go. If we are downloading a chunk, we don't want to measure it, because it would skew the metrics
		// and we would end up with a lot of downloads with only a few photos.
		$should_measure = MetricsController::shouldMeasure() && $request->chunkSlice() === null;
		if ($request->albums()->count() > 0) {
			// We dispatch one event per album.
			foreach ($request->albums() as $album) {
				AlbumDownload::dispatchIf($should_measure, $this->visitorId(), $album->get_id());
			}

			return AlbumBaseArchive::resolve()->do($request->albums(), $request->sizeVariant(), $request->chunkSlice());
		}

		// We dispatch one event per photo.
		foreach ($request->photos() as $photo) {
			PhotoDownload::dispatchIf($should_measure && $request->from_id() !== null, $this->visitorId(), $photo->id, $request->from_id());
		}

		return PhotoBaseArchive::resolve()->do($request->photos(), $request->sizeVariant());
	}

	/**
	 * Return the number of download chunks to expect.
	 */
	public function getChunksCount(ZipChunksRequest $request): ZipChunkData
	{
		$chunked_enabled = $request->configs()->getValueAsBool('download_archive_chunked');
		$chunk_size = max(1, $request->configs()->getValueAsInt('download_archive_chunk_size'));
		$should_measure = MetricsController::shouldMeasure() && $chunked_enabled;

		$total = 0;
		if ($request->albums()->count() > 0) {
			// We dispatch one event per album.
			foreach ($request->albums() as $album) {
				AlbumDownload::dispatchIf($should_measure, $this->visitorId(), $album->get_id());
				$photos = $album->get_photos();
				if ($photos instanceof LengthAwarePaginator) {
					$total += $photos->total();
				} else {
					$total += $photos->count();
				}
			}
			$total_chunks = max(1, (int) ceil($total / $chunk_size));

			return new ZipChunkData(
				total_chunks: $total_chunks,
				total_photos: $total,
			);
		}

		$total = 0;
		foreach ($request->photos() as $photo) {
			PhotoDownload::dispatchIf($should_measure && $request->from_id() !== null, $this->visitorId(), $photo->id, $request->from_id());
			$total++;
		}

		$total_chunks = max(1, (int) ceil($total / $chunk_size));

		return new ZipChunkData(
			total_chunks: $total_chunks,
			total_photos: $total,
		);
	}
}
