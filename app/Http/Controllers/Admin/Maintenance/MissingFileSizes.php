<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Models\SizeVariant;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
			->where('filesize', '=', 0)
			// TODO: remove s3 support here.
			->orderBy('id');
		// Internally, only holds $limit entries at once
		$variants = $variants_query->lazyById(500);

		$generated = 0;

		foreach ($variants as $variant) {
			$variantFile = $variant->getFile();
			if ($variantFile->exists()) {
				$variant->filesize = $variantFile->getFilesize();
				if (!$variant->save()) {
					Log::error('Failed to update filesize for ' . $variantFile->getRelativePath() . '.');
				} else {
					$generated++;
				}
			} else {
				Log::error('No file found at ' . $variantFile->getRelativePath() . '.');
			}
		}
	}

	/**
	 * Check how many images needs to be created.
	 *
	 * @return int
	 */
	public function get(): int
	{
		return SizeVariant::query()
			->where('filesize', '=', 0)
			// TODO: remove s3 support here.
			->count();
	}
}
