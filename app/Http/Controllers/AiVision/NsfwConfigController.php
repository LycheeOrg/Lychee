<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class NsfwConfigController extends Controller
{
	/**
	 * Proxy the NSFW classification service's config endpoint.
	 */
	public function show(): JsonResponse
	{
		$service_url = config('features.ai-vision-service.nsfw-url', '');
		$api_key = config('features.ai-vision-service.nsfw-api-key', '');

		if ($service_url === '') {
			return response()->json([
				'error' => 'NSFW classification service URL is not configured.',
			], 503);
		}

		try {
			$response = Http::withHeaders(['X-API-Key' => $api_key])
				->timeout(10)
				->get($service_url . '/api/nsfw/config');

			if (!$response->successful()) {
				return response()->json([
					'error' => 'NSFW classification service returned an error.',
				], 502);
			}

			return response()->json($response->json());
		} catch (\Exception) {
			return response()->json([
				'error' => 'Could not connect to NSFW classification service.',
			], 503);
		}
	}
}
