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
use App\Services\Image\NsfwDetectionService;
use Illuminate\Support\Facades\Auth;
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

		// If we are not admin, bail out here, as we don't want to expose the service URL to non-admins.
		if (Auth::user()?->may_administrate !== true) {
			return $next($data);
		}

		try {
			$health_data = $this->nsfw_detection_service->checkHealth();

			if ($health_data['status'] !== 'ok' && $health_data['status'] !== 'healthy') {
				$data[] = DiagnosticData::warn(
					'NSFW Classification: Service reported unhealthy status: ' . $health_data['status'],
					self::class,
					[]
				);
			}
		} catch (ExternalComponentMissingException $e) {
			$data[] = DiagnosticData::error(
				'NSFW Classification: ' . $e->getMessage(),
				self::class,
				['Set AI_VISION_NSFW_URL in your .env file.']
			);
		} catch (ExternalComponentFailedException $e) {
			$data[] = DiagnosticData::error(
				'NSFW Classification: ' . $e->getMessage(),
				self::class,
				['Check that the NSFW classification service is running and the URL is correct.']
			);
		}

		return $next($data);
	}
}
