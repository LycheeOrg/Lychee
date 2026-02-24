<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\Handler;
use App\Image\Watermarker;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * We apply watermarks on pictures at upload time.
 */
class ApplyWatermark implements StandalonePipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected readonly Watermarker $watermarker,
	) {
	}

	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Skip if user explicitly opted out
		if ($state->apply_watermark === false) {
			return $next($state);
		}

		if ($this->config_manager->getValueAsBool('watermark_enabled') === false) {
			return $next($state);
		}

		/**
		 * If watermark is enabled but we detected it is not possible, skip but add log warning.
		 */
		if (!$this->watermarker->can_watermark()) {
			Log::error('Could not generate watermark. Please make sure you have Imagick enabled and the watermark photo Id is set.');

			return $next($state);
		}

		// Create remaining size variants if we were able to successfully
		// extract a reference image
		if ($state->source_image?->isLoaded()) {
			$size_variants = $state->getPhoto()->size_variants->toCollection()->filter(fn ($v) => $v !== null);
			foreach ($size_variants as $variant) {
				try {
					$this->watermarker->do($variant);
					// @codeCoverageIgnoreStart
				} catch (\Throwable $t) {
					// Don't re-throw the exception, because we do not want the
					// import to fail completely only due to missing size variants.
					// There are just too many options why the creation of size
					// variants may fail.
					Handler::reportSafely($t);
				}
				// @codeCoverageIgnoreEnd
			}
		}

		return $next($state);
	}
}
