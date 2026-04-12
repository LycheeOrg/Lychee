<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Repositories\ConfigManager;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Support\Facades\Schema;

/**
 * Check if the AI Vision service is properly configured and reachable.
 */
class AiVisionServiceCheck implements DiagnosticPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected readonly FacialRecognitionService $facial_recognition_service,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		// Skip check if AI Vision is disabled
		if (!$this->config_manager->getValueAsBool('ai_vision_enabled')) {
			return $next($data);
		}

		if (!$this->facial_recognition_service->isConfigured()) {
			$data[] = DiagnosticData::error(
				'AI Vision: Service URL is not configured. Set AI_VISION_FACE_URL in your .env file.',
				self::class,
				[]
			);

			return $next($data);
		}

		$this->checkServiceHealth($data);

		return $next($data);
	}

	/**
	 * Check if the AI Vision service health endpoint is reachable and returns proper data.
	 *
	 * @param DiagnosticData[] &$data
	 *
	 * @return void
	 */
	private function checkServiceHealth(array &$data): void
	{
		$service_url = config('features.ai-vision-service.face-url', '');

		try {
			$response = $this->facial_recognition_service->checkHealthRaw(5);

			if (!$response->successful()) {
				$data[] = DiagnosticData::error(
					'AI Vision: Service health check failed with status ' . $response->status() . '. The service may be offline or unreachable.',
					self::class,
					['Check that the AI Vision service is running at: ' . $service_url]
				);

				return;
			}

			$health_data = $response->json();
			if (!is_array($health_data) || !isset($health_data['status'])) {
				$data[] = DiagnosticData::error(
					'AI Vision: Service health endpoint returned invalid response format. Expected JSON with "status" field.',
					self::class,
					['Response: ' . $response->body()]
				);

				return;
			}

			if ($health_data['status'] !== 'ok' && $health_data['status'] !== 'healthy') {
				$data[] = DiagnosticData::warn(
					'AI Vision: Service reported unhealthy status: ' . $health_data['status'],
					self::class,
					[]
				);
			}
		} catch (\Illuminate\Http\Client\ConnectionException $e) {
			$data[] = DiagnosticData::error(
				'AI Vision: Could not connect to service at ' . rtrim($service_url, '/') . '/health',
				self::class,
				['Check that the AI Vision service is running and the URL is correct.', $e->getMessage()]
			);
		} catch (\Exception $e) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				'AI Vision: Service health check failed with error: ' . $e->getMessage(),
				self::class,
				[]
			);
			// @codeCoverageIgnoreEnd
		}
	}
}
