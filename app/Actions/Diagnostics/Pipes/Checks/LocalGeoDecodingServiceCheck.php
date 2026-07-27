<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

/**
 * Check that a configured local reverse geo-decoding service is reachable.
 */
class LocalGeoDecodingServiceCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		// Mirrors the gate in Geodecoder::localBaseUrl(): the local connector only
		// applies when the v8 feature is active.
		if (config('features.v8') !== true) {
			return $next($data);
		}

		$base_url = config('services.local-geo-decoding.base_url', '');
		if ($base_url === '') {
			return $next($data);
		}

		// If we are not admin, bail out here, as we don't want to expose the service URL to non-admins.
		if (Auth::user()?->may_administrate !== true) {
			return $next($data);
		}

		try {
			$response = Http::timeout(5)->get($base_url . '/health');
		} catch (\Exception $e) {
			$data[] = DiagnosticData::error(
				'Local Reverse Geo-Decoding: Could not connect to the service: ' . $e->getMessage(),
				self::class,
				['Check that the local reverse geo-decoding service is running and LOCAL_GEO_DECODING_URL is correct.']
			);

			return $next($data);
		}

		if (!in_array($response->status(), [200, 204], true)) {
			$data[] = DiagnosticData::error(
				'Local Reverse Geo-Decoding: Service health check returned HTTP ' . $response->status() . '.',
				self::class,
				['Check that the local reverse geo-decoding service is running and healthy.']
			);
		}

		return $next($data);
	}
}
