<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Profiling;

use App\Constants\FileSystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Safe\json_decode;

/**
 * Bounds `storage/profiling` growth by keeping only the newest
 * `features.memory-profiler-max-traces` traces (FR-053-07). Shared by
 * {@see \App\Console\Commands\Profiling\PruneTraces} and
 * {@see \App\Http\Controllers\Admin\ProfilerController::prune()}.
 *
 * Each trace spans two files pairs written by different components:
 * our own `lychee-{basename}.json` sidecar (route/method/status/etc.,
 * written by {@see \App\Http\Middleware\MemoryProfiler}) and the `spx`
 * extension's own `{spx_report_key}.json` + `{spx_report_key}.txt.gz`
 * report files, correlated via `spx_report_key`. Both must be deleted
 * together to avoid orphaning SPX's own report files.
 */
class TracePruner
{
	private const SIDECAR_PREFIX = 'lychee-';

	/**
	 * @return int the number of traces removed
	 */
	public function prune(): int
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$max_traces = (int) config('features.memory-profiler-max-traces');

		$sidecars = collect($disk->files())
			->filter(static fn (string $file): bool => str_starts_with($file, self::SIDECAR_PREFIX) && str_ends_with($file, '.json'))
			->values();

		if ($sidecars->count() <= $max_traces) {
			return 0;
		}

		$sorted = $sidecars->sortByDesc(function (string $sidecar) use ($disk): string {
			/** @var array<string,mixed> $meta */
			$meta = json_decode($disk->get($sidecar), true);

			return (string) ($meta['created_at'] ?? '');
		})->values();

		$to_remove = $sorted->slice($max_traces);

		foreach ($to_remove as $sidecar) {
			/** @var array<string,mixed> $meta */
			$meta = json_decode($disk->get($sidecar), true);
			$spx_report_key = $meta['spx_report_key'] ?? null;

			$disk->delete($sidecar);

			if (\is_string($spx_report_key) && $spx_report_key !== '') {
				foreach (['.json', '.txt.gz'] as $extension) {
					$spx_file = $spx_report_key . $extension;
					if ($disk->exists($spx_file)) {
						$disk->delete($spx_file);
					}
				}
			}
		}

		$removed_count = $to_remove->count();

		Log::info('memory_profiler.pruned', [
			'removed_count' => $removed_count,
			'remaining_count' => $max_traces,
		]);

		return $removed_count;
	}
}
