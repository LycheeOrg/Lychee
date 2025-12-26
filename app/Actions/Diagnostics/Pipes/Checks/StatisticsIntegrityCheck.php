<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Http\Resources\Diagnostics\StatisticsCheckResource;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;

/**
 * Check whether or not there are photos or albums without statistics.
 */
class StatisticsIntegrityCheck implements DiagnosticPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$check = $this->get();

		if ($check->missing_albums > 0) {
			$data[] = DiagnosticData::warn(sprintf('There are %d albums without statistics.', $check->missing_albums), self::class,
				['Go to the maintenance page to fix this.']);
		}

		if ($check->missing_albums > 0) {
			$data[] = DiagnosticData::warn(sprintf('There are %d photos without statistics.', $check->missing_photos), self::class,
				['Go to the maintenance page to fix this.']);
		}

		return $next($data);
	}

	public function get(): StatisticsCheckResource
	{
		// Just skip the check, we don't care.
		if (!$this->config_manager->getValueAsBool('metrics_enabled')) {
			return new StatisticsCheckResource(0, 0);
		}

		$num_albums = DB::table('base_albums')->leftJoin('statistics', 'base_albums.id', '=', 'statistics.album_id')
				->whereNull('statistics.id')
				->count();
		$num_photos = DB::table('photos')->leftJoin('statistics', 'photos.id', '=', 'statistics.photo_id')
			->whereNull('statistics.id')
			->count();

		return new StatisticsCheckResource($num_albums, $num_photos);
	}
}