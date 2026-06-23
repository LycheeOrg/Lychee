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
use App\Services\Image\NsfwDetectionService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Schema;

class AiVisionNsfwServiceCheck implements DiagnosticPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected readonly NsfwDetectionService $nsfw_detection_service,
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

		if (!$this->config_manager->getValueAsBool('ai_vision_enabled')) {
			return $next($data);
		}

		if (!$this->config_manager->getValueAsBool('ai_vision_nsfw_enabled')) {
			return $next($data);
		}

		if (!$this->nsfw_detection_service->isConfigured()) {
			$data[] = DiagnosticData::error(
				'NSFW Classification: Service URL is not configured. Set AI_VISION_NSFW_URL in your .env file.',
				self::class,
				[]
			);

			return $next($data);
		}

		$this->checkServiceHealth($data);

		return $next($data);
	}

	/**
	 * @param DiagnosticData[] &$data
	 */
	private function checkServiceHealth(array &$data): void
	{
		$service_url = config('features.ai-vision-service.nsfw-url', '');

		try {
			$response = $this->nsfw_detection_service->checkHealthRaw(5);

			if (!$response->successful()) {
				$data[] = DiagnosticData::error(
					'NSFW Classification: Service health check failed with status ' . $response->status() . '. The service may be offline or unreachable.',
					self::class,
					['Check that the NSFW classification service is running at: ' . $service_url]
				);

				return;
			}

			$health_data = $response->json();
			if (!is_array($health_data) || !isset($health_data['status'])) {
				$data[] = DiagnosticData::error(
					'NSFW Classification: Service health endpoint returned invalid response format. Expected JSON with "status" field.',
					self::class,
					['Response: ' . $response->body()]
				);

				return;
			}

			if ($health_data['status'] !== 'ok' && $health_data['status'] !== 'healthy') {
				$data[] = DiagnosticData::warn(
					'NSFW Classification: Service reported unhealthy status: ' . $health_data['status'],
					self::class,
					[]
				);
			}
		} catch (ConnectionException $e) {
			$data[] = DiagnosticData::error(
				'NSFW Classification: Could not connect to service at ' . rtrim($service_url, '/') . '/api/nsfw/health',
				self::class,
				['Check that the NSFW classification service is running and the URL is correct.', $e->getMessage()]
			);
		} catch (\Exception $e) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				'NSFW Classification: Service health check failed with error: ' . $e->getMessage(),
				self::class,
				[]
			);
			// @codeCoverageIgnoreEnd
		}
	}
}
