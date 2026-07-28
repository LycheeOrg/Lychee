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
 * `features.memory-profiler-max-traces` trace pairs (FR-053-07).
 * Shared by {@see \App\Console\Commands\Profiling\PruneTraces} and
 * {@see \App\Http\Controllers\Admin\ProfilerController::prune()}.
 */
class TracePruner
{
	/**
	 * @return int the number of trace pairs removed
	 */
	public function prune(): int
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$max_traces = (int) config('features.memory-profiler-max-traces');

		$basenames = collect($disk->files())
			->filter(static fn (string $file): bool => str_ends_with($file, '.pprof'))
			->map(static fn (string $file): string => substr($file, 0, -\strlen('.pprof')))
			->values();

		if ($basenames->count() <= $max_traces) {
			return 0;
		}

		$sorted = $basenames->sortByDesc(function (string $basename) use ($disk): string {
			$json_file = $basename . '.json';
			if (!$disk->exists($json_file)) {
				return '';
			}

			/** @var array<string,mixed> $meta */
			$meta = json_decode($disk->get($json_file), true);

			return (string) ($meta['created_at'] ?? '');
		})->values();

		$to_remove = $sorted->slice($max_traces);

		foreach ($to_remove as $basename) {
			foreach (['.pprof', '.json', '.svg'] as $extension) {
				if ($disk->exists($basename . $extension)) {
					$disk->delete($basename . $extension);
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
