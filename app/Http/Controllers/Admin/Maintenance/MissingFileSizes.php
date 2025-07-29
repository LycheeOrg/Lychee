<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\StorageDiskType;
use App\Events\AlbumRouteCacheUpdated;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\Configs;
use App\Models\SizeVariant;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use League\Flysystem\UnableToRetrieveMetadata;

/**
 * We may be missing some file sizes because of generation problems,
 * transfer of files, or other.
 * This module aims to solve this issue.
 */
class MissingFileSizes extends Controller
{
	/**
	 * Fetch the file size of existing size variants when the data is not in the DB
	 * Process by chunks of 500.
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$variants_query = SizeVariant::query()
			->where('storage_disk', '=', StorageDiskType::LOCAL)
			->where('filesize', '=', 0)
			->orderBy('id');
		// Internally, only holds $limit entries at once
		$variants = $variants_query->limit(Configs::getValueAsInt('maintenance_processing_limit'))->lazyById(100);

		$generated = 0;

		foreach ($variants as $variant) {
			// @codeCoverageIgnoreStart
			$variant_file = $variant->getFile();
			if ($variant_file->exists()) {
				try {
					$variant->filesize = $variant_file->getFilesize();
					if (!$variant->save()) {
						Log::error('Failed to update filesize for ' . $variant_file->getRelativePath() . '.');
					} else {
						$generated++;
					}
				} catch (UnableToRetrieveMetadata) {
					Log::error($variant->id . ' : Failed to get filesize for ' . $variant_file->getRelativePath() . '.');
				}
			} else {
				Log::error($variant->id . ' : No file found at ' . $variant_file->getRelativePath() . '.');
			}
			// @codeCoverageIgnoreEnd
		}

		AlbumRouteCacheUpdated::dispatch();
	}

	/**
	 * Check how many images have missing file sizes..
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		return SizeVariant::query()
			->where('filesize', '=', 0)
			// TODO: remove s3 support here.
			->count();
	}
}
