<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Schema;

/**
 * We check wether the config is set to use Cache and if we are using temporary URLS.
 */
class CacheTemporaryUrlCheck implements DiagnosticPipe
{
	public function __construct(
		private ConfigManager $config_manager,
	)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		if (!$this->config_manager->getValueAsBool('cache_enabled')) {
			return $next($data);
		}

		if (!$this->config_manager->getValueAsBool('temporary_image_link_enabled')) {
			return $next($data);
		}

		$cache_ttl_in_seconds = $this->config_manager->getValueAsInt('cache_ttl');
		$temporary_image_link_life_in_seconds = $this->config_manager->getValueAsInt('temporary_image_link_life_in_seconds');

		if ($cache_ttl_in_seconds > $temporary_image_link_life_in_seconds) {
			$data[] = DiagnosticData::error('Response cache lifetime is longer than Image temporary URL lifetime.', self::class,
				['Due to response caching, the temporary URL will be valid for a shorter time than the cache.',
					'When close to the response cache expiration time, the temporary URL will be invalidated and the image will not be displayed.',
					'To solve this issue either: disable response caching, or disable temporary URl, or shorten the response cache lifetime, or set the temporary URL lifetime to a longer time.']);
		}

		return $next($data);
	}
}
