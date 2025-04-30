<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Facades\Helpers;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;
use function Safe\exec;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

/**
 * Verify that we have some image optimization available if enabled.
 */
class ImageOptCheck implements DiagnosticPipe
{
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

		$tools = [];
		$tools[] = new Cwebp();
		$tools[] = new Gifsicle();
		$tools[] = new Jpegoptim();
		$tools[] = new Optipng();
		$tools[] = new Pngquant();
		$tools[] = new Svgo();

		$settings = Configs::get();
		if (!isset($settings['lossless_optimization']) || $settings['lossless_optimization'] !== '1') {
			return $next($data);
		}
		// @codeCoverageIgnoreStart

		$binary_path = config('image-optimizer.binary_path');

		if ($binary_path !== '' && substr($binary_path, -1) !== DIRECTORY_SEPARATOR) {
			$binary_path .= DIRECTORY_SEPARATOR;
		}

		if (Helpers::isExecAvailable()) {
			foreach ($tools as $tool) {
				$path = exec('command -v ' . $binary_path . $tool->binaryName());
				if ($path === '') {
					$data[] = DiagnosticData::warn('lossless_optimization set to 1 but ' . $binary_path . $tool->binaryName() . ' not found!', self::class);
				}
			}
		} else {
			$data[] = DiagnosticData::warn('lossless_optimization set to 1 but exec() is not enabled.', self::class);
		}

		return $next($data);
		// @codeCoverageIgnoreEnd
	}
}
