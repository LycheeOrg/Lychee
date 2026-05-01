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
 * In debug mode, expose the AI Vision runtime configuration in diagnostics.
 */
class AiVisionServiceConfigCheck implements DiagnosticPipe
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
		if (config('app.debug') !== true) {
			return $next($data);
		}

		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		if ($this->config_manager->getValueAsBool('ai_vision_enabled') !== true) {
			return $next($data);
		}

		if (!$this->facial_recognition_service->isConfigured()) {
			return $next($data);
		}

		$configuration = $this->facial_recognition_service->getConfiguration();
		if ($configuration === null) {
			$data[] = DiagnosticData::warn(
				'AI Vision: Could not fetch runtime configuration from the service while APP_DEBUG is enabled.',
				self::class,
				[]
			);

			return $next($data);
		}

		$details = [];
		foreach ($configuration as $key => $value) {
			$details[] = $key . ': ' . $value;
		}

		$data[] = DiagnosticData::info(
			'AI Vision: Runtime configuration from service (debug mode).',
			self::class,
			$details
		);

		return $next($data);
	}
}
