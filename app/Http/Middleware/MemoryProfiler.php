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
use App\Services\Profiling\MemprofRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Safe\json_encode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures a memory-allocation profile (via {@see MemprofRecorder}) for the
 * whole lifetime of a request, when the `memory-profiler` feature flag is
 * enabled and the `memprof` extension is loaded. See Feature 053.
 *
 * State is intentionally carried on the {@see Request} instance (attributes)
 * rather than on `$this`, because Laravel resolves a **new** middleware
 * instance from the container for {@see self::terminate()} — the instance
 * that ran {@see self::handle()} is not the same object (this also keeps
 * the class free of per-request mutable instance state, which matters for
 * Octane-hosted deployments; see NFR-053-06 / ADR-0008 for the separate,
 * still-open question of whether the `memprof` extension's own internal
 * state is itself request-scoped under Octane).
 */
class MemoryProfiler
{
	private const ATTR_START_TIME = 'memory_profiler.start_time';

	public function __construct(
		private MemprofRecorder $recorder,
	) {
	}

	public function handle(Request $request, \Closure $next): mixed
	{
		if (!Features::active('memory-profiler') || !$this->recorder->isAvailable()) {
			return $next($request);
		}

		$request->attributes->set(self::ATTR_START_TIME, microtime(true));
		$this->recorder->enable();

		return $next($request);
	}

	public function terminate(Request $request, Response $response): void
	{
		$start_time = $request->attributes->get(self::ATTR_START_TIME);
		if (!\is_float($start_time)) {
			return;
		}

		$this->recorder->disable();

		try {
			$disk = Storage::disk(FileSystem::PROFILING);
			$basename = $this->generateBasename();

			$this->recorder->dumpPprof($disk->path($basename . '.pprof'));

			$meta = new ProfilingTraceMeta(
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
