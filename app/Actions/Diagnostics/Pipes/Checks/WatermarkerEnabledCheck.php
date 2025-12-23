<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\DTO\DiagnosticDTO;
use App\Enum\SizeVariantType;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\Schema;

/**
 * Check if the watermarker is properly configured and enabled.
 */
class WatermarkerEnabledCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		if (!Schema::hasTable('configs') || !Schema::hasTable('size_variants')) {
			return $next($data);
		}

		if (!$data->config_manager->getValueAsBool('watermark_enabled')) {
			return $next($data);
		}

		$this->validateImagick($data);
		$this->validatePhotoId($data);

		return $next($data);
	}

	/**
	 * Validate that Imagick is available and loaded..
	 *
	 * @param DiagnosticDTO &$data
	 *
	 * @return void
	 */
	private function validateImagick(DiagnosticDTO &$data): void
	{
		$is_imagick_loaded = extension_loaded('imagick');
		if (!$data->config_manager->getValueAsBool('imagick')) {
			$data->data[] = DiagnosticData::warn(
				'Watermarker: imagick is not enabled in your settings. Watermarking step will be skipped.',
				self::class,
				$is_imagick_loaded ? [] : ['Imagick is not available on your php install. Make sure the extension is loaded.']
			);

			return;
		}

		if (!$is_imagick_loaded) {
			// @codeCoverageIgnoreStart
			$data->data[] = DiagnosticData::warn(
				'Watermarker: php imagick extension is not loaded. Watermarking step will be skipped.',
				self::class,
				[]
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Validate if the PhotoId provided is correct.
	 *
	 * @param DiagnosticDTO &$data
	 *
	 * @return void
	 */
	private function validatePhotoId(DiagnosticDTO &$data): void
	{
		$watermark_photo_id = $data->config_manager->getValueAsString('watermark_photo_id');
		if ($watermark_photo_id === '') {
			$data->data[] = DiagnosticData::warn(
				'Watermarker: photo_id is not provided. Watermarking step will be skipped.',
				self::class,
				[]
			);

			return;
		}

		$watermark = SizeVariant::query()
				->where('photo_id', '=', $watermark_photo_id)
				->where('type', '=', SizeVariantType::ORIGINAL->value)
				->first();
		if ($watermark === null) {
			// If no watermark is found, we cannot watermark
			$data->data[] = DiagnosticData::error(
				'Watermarker: the photo_id provided does not match any photo. Watermarking step will be skipped.',
				self::class,
				[]
			);
		}
	}
}
