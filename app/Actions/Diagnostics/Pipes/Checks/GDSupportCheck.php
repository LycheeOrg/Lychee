<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Image\Handlers\GdHandler;
use App\Repositories\ConfigManager;

/**
 * Verify that GD support the correct images extensions.
 */
class GDSupportCheck implements DiagnosticPipe
{
	public const LOSSLESS_WEBP_UNSUPPORTED = 'Lossless WebP is requested (compression_quality = 0, size_variant_format = webp) but this PHP gd build cannot encode it (libgd < 2.3.3). Uploads will fail: set compression_quality to 1-100 or enable Imagick.';

	public function __construct(protected ConfigManager $config_manager)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (function_exists('gd_info')) {
			$gd_version = gd_info();
			if (!$gd_version['JPEG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without jpeg support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gd_version['PNG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without png support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (
				!$gd_version['GIF Read Support'] ||
				!$gd_version['GIF Create Support']
			) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without full gif support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gd_version['WebP Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without WebP support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if ($this->isLosslessWebpUnsupported()) {
				$data[] = DiagnosticData::warn(self::LOSSLESS_WEBP_UNSUPPORTED, self::class);
			}
		}

		return $next($data);
	}

	/**
	 * Lossless WebP is requested for GD-generated size variants, but GD cannot encode it.
	 */
	private function isLosslessWebpUnsupported(): bool
	{
		// hasKey(): the config may not exist yet while migrations are pending.
		return $this->config_manager->hasKey('size_variant_format') &&
			!$this->config_manager->hasImagick() &&
			$this->config_manager->getValueAsString('size_variant_format') === 'webp' &&
			$this->config_manager->getValueAsInt('compression_quality') === 0 &&
			!$this->supportsLosslessWebp();
	}

	protected function supportsLosslessWebp(): bool
	{
		return GdHandler::supportsLosslessWebp();
	}
}
