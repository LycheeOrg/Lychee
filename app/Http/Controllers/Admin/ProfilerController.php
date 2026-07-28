<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Constants\FileSystem;
use App\DTO\Profiling\ProfilingTraceMeta;
use App\Services\Profiling\TracePruner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use function Safe\json_decode;

/**
 * Owner-only admin surface for browsing memory-profiler traces
 * (Feature 053). Blade-only, no Vue/API surface (NFR-053-03).
 *
 * Traces themselves are captured by the `spx` extension (via
 * {@see \App\Http\Middleware\MemoryProfiler}); this controller only lists
 * our own metadata sidecars (`lychee-*.json`) and links out to SPX's own
 * analysis screen (protected by `spx.http_key` / `spx.http_ip_whitelist`,
 * not by this page's owner-only gate — see ADR-0008) for the call-graph
 * itself. There is no in-app rendering.
 */
class ProfilerController extends Controller
{
	private const SIDECAR_PREFIX = 'lychee-';

	/**
	 * List every trace currently in `storage/profiling` (FR-053-03).
	 */
	public function index(): View
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$spx_key = config('features.memory-profiler-spx-key');

		$traces = collect($disk->files())
			->filter(static fn (string $file): bool => str_starts_with($file, self::SIDECAR_PREFIX) && str_ends_with($file, '.json'))
			->map(function (string $json_file) use ($disk, $spx_key): array {
				/** @var array<string,mixed> $decoded */
				$decoded = json_decode($disk->get($json_file), true);
				$meta = ProfilingTraceMeta::fromJsonArray($decoded);

				return [
					'meta' => $meta,
					'spx_url' => $meta->spx_report_key !== null && \is_string($spx_key) && $spx_key !== ''
						? $this->buildSpxAnalysisUrl($meta->spx_report_key, $spx_key)
						: null,
				];
			})
			->sortByDesc(static fn (array $trace): string => $trace['meta']->created_at)
			->values();

		return view('admin.profiler.index', [
			'traces' => $traces,
			'is_octane' => getenv('LARAVEL_OCTANE') !== false,
			'spx_key_configured' => \is_string($spx_key) && $spx_key !== '',
		]);
	}

	/**
	 * Manually trigger pruning (FR-053-07), invoked from the admin page's
	 * "Prune old traces" button. Shares {@see TracePruner} with the
	 * scheduled/console command (CLI-053-01).
	 */
	public function prune(TracePruner $pruner): RedirectResponse
	{
		$pruner->prune();

		return redirect()->route('admin.profiler.index');
	}

	/**
	 * Builds the URL for SPX's own analysis screen for a given report key,
	 * per SPX's documented pattern: `/?SPX_UI_URI=/report.html&key=<report key>`.
	 * `SPX_KEY` must additionally match the extension's own `spx.http_key`
	 * ini value for SPX to honour the request at all (see ADR-0008).
	 */
	private function buildSpxAnalysisUrl(string $spx_report_key, string $spx_key): string
	{
		return url('/') . '?' . http_build_query([
			'SPX_UI_URI' => '/report.html',
			'SPX_KEY' => $spx_key,
			'key' => $spx_report_key,
		]);
	}
}
