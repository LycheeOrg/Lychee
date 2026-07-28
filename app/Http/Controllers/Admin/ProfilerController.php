<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Constants\FileSystem;
use App\DTO\Profiling\ProfilingTraceMeta;
use App\Services\Profiling\PprofRenderer;
use App\Services\Profiling\TracePruner;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use function Safe\json_decode;
use function Safe\preg_match;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Owner-only admin surface for browsing memory-profiler traces
 * (Feature 053). Blade-only, no Vue/API surface (NFR-053-03).
 */
class ProfilerController extends Controller
{
	/**
	 * List every trace pair currently in `storage/profiling` (FR-053-03).
	 */
	public function index(): View
	{
		$disk = Storage::disk(FileSystem::PROFILING);

		$traces = collect($disk->files())
			->filter(static fn (string $file): bool => str_ends_with($file, '.json'))
			->map(function (string $json_file) use ($disk): array {
				$basename = substr($json_file, 0, -\strlen('.json'));
				/** @var array<string,mixed> $decoded */
				$decoded = json_decode($disk->get($json_file), true);
				$meta = ProfilingTraceMeta::fromJsonArray($decoded);
				$pprof_file = $basename . '.pprof';

				return [
					'basename' => $basename,
					'meta' => $meta,
					'size' => $disk->exists($pprof_file) ? $disk->size($pprof_file) : 0,
				];
			})
			->sortByDesc(static fn (array $trace): string => $trace['meta']->created_at)
			->values();

		return view('admin.profiler.index', [
			'traces' => $traces,
			'is_octane' => getenv('LARAVEL_OCTANE') !== false,
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
	 * Render (or return a cached render of) a trace's SVG call-graph
	 * (FR-053-04). `$trace` is resolved against an allow-list of basenames
	 * actually present on disk before ever touching the filesystem
	 * (NFR-053-04) — it is never used to build a path directly.
	 */
	public function svg(string $trace, PprofRenderer $renderer): View
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$basename = $this->resolveTraceBasename($trace, $disk);

		$meta = $disk->exists($basename . '.json')
			? ProfilingTraceMeta::fromJsonArray(json_decode($disk->get($basename . '.json'), true))
			: null;

		if ($disk->exists($basename . '.svg')) {
			return view('admin.profiler.show', [
				'trace' => $basename,
				'meta' => $meta,
				'svg' => $disk->get($basename . '.svg'),
				'error_message' => null,
			]);
		}

		$result = $renderer->render($disk->path($basename . '.pprof'));

		if (!$result->success) {
			Log::error('memory_profiler.svg_render_failed', [
				'trace_file' => $basename,
				'reason' => $result->reason,
			]);

			return view('admin.profiler.show', [
				'trace' => $basename,
				'meta' => $meta,
				'svg' => null,
				'error_message' => $result->error_message,
			]);
		}

		$disk->put($basename . '.svg', $result->svg);

		return view('admin.profiler.show', [
			'trace' => $basename,
			'meta' => $meta,
			'svg' => $result->svg,
			'error_message' => null,
		]);
	}

	/**
	 * Stream the raw `.pprof` dump for a trace, for operators who prefer to
	 * inspect it with their own tooling (e.g. `pprof --web`).
	 */
	public function download(string $trace): \Symfony\Component\HttpFoundation\BinaryFileResponse
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$basename = $this->resolveTraceBasename($trace, $disk);

		return response()->download($disk->path($basename . '.pprof'), $basename . '.pprof');
	}

	/**
	 * Resolves the `{trace}` route parameter to a basename that actually
	 * exists on the `profiling` disk, rejecting anything else with a 404
	 * (NFR-053-04). The regex allow-list alone rules out path separators
	 * and `..` traversal segments.
	 */
	private function resolveTraceBasename(string $trace, FilesystemAdapter $disk): string
	{
		if (preg_match('/^[A-Za-z0-9_\-]+$/', $trace) !== 1 || !$disk->exists($trace . '.pprof')) {
			throw new NotFoundHttpException('Trace not found.');
		}

		return $trace;
	}
}
