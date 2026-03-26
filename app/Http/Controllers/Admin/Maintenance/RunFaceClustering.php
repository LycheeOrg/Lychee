<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Admin maintenance controller to trigger face clustering in the AI Vision service.
 *
 * POST /Maintenance::runFaceClustering  — dispatch clustering job to Python service
 */
class RunFaceClustering extends Controller
{
	/**
	 * Trigger face clustering in the AI Vision Python service.
	 *
	 * @return JsonResponse
	 */
	public function do(MaintenanceRequest $request): JsonResponse
	{
		$service_url = config('features.ai-vision.face-url', '');
		$api_key = config('features.ai-vision.face-api-key', '');

		if ($service_url === '') {
			return response()->json(['status' => 'error', 'message' => 'AI Vision service not configured.'], 503);
		}

		try {
			$response = Http::withHeaders(['X-API-Key' => $api_key])
				->post($service_url . '/cluster');

			if ($response->status() === 202) {
				return response()->json(['status' => 'dispatched', 'message' => 'Clustering job accepted; results will be sent via callback.'], 202);
			}

			if ($response->successful()) {
				return response()->json(['status' => 'dispatched'], 200);
			}

			Log::warning('RunFaceClustering: /cluster returned HTTP ' . $response->status() . '.');

			return response()->json(['status' => 'error', 'message' => 'Clustering service returned HTTP ' . $response->status()], 503);
		} catch (\Exception $e) {
			Log::warning('RunFaceClustering: request failed: ' . $e->getMessage());

			return response()->json(['status' => 'error', 'message' => $e->getMessage()], 503);
		}
	}
}
