<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\ExtractColoursJob;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use LycheeVerify\Verify;

/**
 * Handles missing palettes for photos.
 */
class MissingPalettes extends Controller
{
	/**
	 * Count photos without a palette.
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (!resolve(Verify::class)->check() || !Configs::getValueAsBool('enable_colour_extractions')) {
			return 0;
		}

		return Photo::query()
			->where('type', 'like', 'image/%')
			->whereDoesntHave('palette')
			->count();
	}

	/**
	 * Generate missing palettes for photos in chunks.
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		if (!resolve(Verify::class)->check() || !Configs::getValueAsBool('enable_colour_extractions')) {
			return;
		}

		$limit = Configs::getValueAsInt('maintenance_processing_limit');
		$photos = Photo::with(['size_variants'])
			->whereDoesntHave('palette')
			->where('type', 'like', 'image/%')
			->orderBy('id')
			->lazyById($limit);

		foreach ($photos as $photo) {
			ExtractColoursJob::dispatchSync($photo);
		}
	}
}
