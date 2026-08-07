<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Assets\Features;
use App\Constants\FileSystem;
use App\DTO\Profiling\ProfilingTraceMeta;
use App\Services\Profiling\SpxRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Safe\json_encode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bounds a memory-profiling span (via {@see SpxRecorder}, i.e. the `spx`
 * PECL extension's `spx_profiler_start()`/`spx_profiler_stop()`) around the
 * whole lifetime of a request, when the `memory-profiler` feature flag is
 * enabled and the extension is loaded. See Feature 053.
 *
 * Manual start/stop (rather than SPX's own ini-only "always profiling" mode)
 * is deliberate: it guarantees a correct per-request span regardless of
 * whether the host runtime (Octane/FrankenPHP's persistent worker model)
 * fires fresh Zend request-lifecycle hooks per HTTP request — see
 * NFR-053-06 / ADR-0008. Requires `spx.http_profiling_auto_start=0` (set by
 * the Docker startup script, `docker/scripts/06-configure-profiler.sh`).
 *
 * State is intentionally carried on the {@see Request} instance (attributes)
 * rather than on `$this`, because Laravel resolves a **new** middleware
 * instance from the container for {@see self::terminate()} — the instance
 * that ran {@see self::handle()} is not the same object.
 */
class MemoryProfiler
{
	private const ATTR_START_TIME = 'memory_profiler.start_time';

	public function __construct(
		private SpxRecorder $recorder,
	) {
	}

	public function handle(Request $request, \Closure $next): mixed
	{
		if (!Features::active('memory-profiler') || !$this->recorder->isAvailable()) {
			return $next($request);
		}

		$request->attributes->set(self::ATTR_START_TIME, microtime(true));
		$this->recorder->start();

		return $next($request);
	}

	public function terminate(Request $request, Response $response): void
	{
		$start_time = $request->attributes->get(self::ATTR_START_TIME);
		if (!\is_float($start_time)) {
			return;
		}

		$spx_report_key = $this->recorder->stop();

		try {
			$disk = Storage::disk(FileSystem::PROFILING);
			$basename = 'lychee-' . $this->generateBasename();

			$meta = new ProfilingTraceMeta(
				spx_report_key: $spx_report_key,
				route_name: $request->route()?->getName(),
				method: $request->getMethod(),
				path: $request->path(),
				status_code: $response->getStatusCode(),
				duration_ms: (microtime(true) - $start_time) * 1000,
				peak_memory_bytes: memory_get_peak_usage(true),
				user_id: Auth::id(),
				created_at: now()->toIso8601String(),
			);
			$disk->put($basename . '.json', json_encode($meta->toJsonArray(), \JSON_PRETTY_PRINT));
		} catch (\Throwable $e) {
			Log::error('memory_profiler.dump_failed', [
				'route' => $request->route()?->getName(),
				'exception_message' => $e->getMessage(),
			]);
		}
	}

	private function generateBasename(): string
	{
		return now()->format('Ymd_His') . '_' . Str::random(8);
	}
}
