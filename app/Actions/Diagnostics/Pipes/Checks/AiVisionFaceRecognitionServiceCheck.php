<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\ExternalComponentMissingException;
use App\Repositories\ConfigManager;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Support\Facades\Schema;

/**
 * Check if the AI Vision service is properly configured and reachable.
 */
class AiVisionFaceRecognitionServiceCheck implements DiagnosticPipe
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

		if (!$this->config_manager->getValueAsBool('ai_vision_enabled')) {
			return $next($data);
		}

		try {
			$health_data = $this->facial_recognition_service->checkHealth();

			if ($health_data['status'] !== 'ok' && $health_data['status'] !== 'healthy') {
				$data[] = DiagnosticData::warn(
					'AI Vision: Service reported unhealthy status: ' . $health_data['status'],
					self::class,
					[]
				);
			}
		} catch (ExternalComponentMissingException $e) {
			$data[] = DiagnosticData::error(
				'AI Vision: ' . $e->getMessage(),
				self::class,
				['Set AI_VISION_FACE_URL in your .env file.']
			);
		} catch (ExternalComponentFailedException $e) {
			$data[] = DiagnosticData::error(
				'AI Vision: ' . $e->getMessage(),
				self::class,
				['Check that the AI Vision service is running and the URL is correct.']
			);
		}

		return $next($data);
	}
}
