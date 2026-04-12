<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Requests\Album\ZipChunksRequest;
use App\Repositories\ConfigManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ZipChunksController
{
	public function index(ZipChunksRequest $request): JsonResponse
	{
		$config_manager = app(ConfigManager::class);
		$chunked_enabled = $config_manager->getValueAsBool('download_archive_chunked');
		$chunk_size = $config_manager->getValueAsInt('download_archive_chunk_size');

		$total = 0;
		foreach ($request->albums() as $album) {
			$photos = $album->get_photos();
			if ($photos instanceof LengthAwarePaginator) {
				$total += $photos->total();
			} else {
				$total += $photos->count();
			}
		}
		foreach ($request->photos() as $ignored) {
			$total++;
		}

		if (!$chunked_enabled || $chunk_size <= 0) {
			return response()->json(['total_chunks' => 1, 'total_photos' => $total]);
		}

		$total_chunks = max(1, (int) ceil($total / $chunk_size));

		return response()->json(['total_chunks' => $total_chunks, 'total_photos' => $total]);
	}
}
