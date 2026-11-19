<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\SizeVariantFormat;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Schema;

/**
 * Report when this system cannot read or write JPEG XL.
 */
class ImagickJxlCheck implements DiagnosticPipe
{
	public const UNSUPPORTED = 'JPEG XL is not supported by this system: .jxl uploads will fail.';
	public const UNSUPPORTED_WITH_JXL_FORMAT = 'size_variant_format is jxl but JPEG XL is not supported by this system: every upload will fail.';
	public const DETAILS = ['JPEG XL requires Imagick enabled and ImageMagick built with JXL support (check `magick -list format`).'];

	public function __construct(protected readonly ConfigManager $config_manager)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if ($this->supportsJxl()) {
			return $next($data);
		}

		$data[] = $this->config_manager->getValueAsEnum('size_variant_format', SizeVariantFormat::class) === SizeVariantFormat::JXL
			? DiagnosticData::error(self::UNSUPPORTED_WITH_JXL_FORMAT, self::class, self::DETAILS)
			: DiagnosticData::info(self::UNSUPPORTED, self::class, self::DETAILS);

		return $next($data);
	}

	protected function supportsJxl(): bool
	{
		return $this->config_manager->hasJxl();
	}
}
