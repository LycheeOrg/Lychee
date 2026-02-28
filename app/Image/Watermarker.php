<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Image;

use App\Assets\WatermarkGroupedWithRandomSuffixNamingStrategy;
use App\DTO\CreateSizeVariantFlags;
use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Image\Handlers\ImagickHandler;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Watermarker
{
	private string $watermark_photo_id = '';
	private ?SizeVariant $size_variant_watermark = null;
	private WatermarkGroupedWithRandomSuffixNamingStrategy $naming_strategy;

	/**
	 * Create a watermarker.
	 */
	public function __construct()
	{
		$this->naming_strategy = new WatermarkGroupedWithRandomSuffixNamingStrategy();

		if (!$this->is_watermark_enabled()) {
			return;
		}

		if (!$this->check_watermark_image()) {
			return;
		}
	}

	/**
	 * Check if configuration is set to allow watermark.
	 *
	 * @return bool
	 */
	private function is_watermark_enabled(): bool
	{
		$config_manager = resolve(ConfigManager::class);
		$is_enabled = $config_manager->getValueAsBool('watermark_enabled');
		$is_imagick_enabled = $config_manager->getValueAsBool('imagick');
		$is_imagick_loaded = extension_loaded('imagick');

		if (!$is_enabled || !$is_imagick_enabled || !$is_imagick_loaded) {
			// If watermarking is not enabled or Imagick is not available, we cannot watermark
			// Exit now.
			return false;
		}

		$this->watermark_photo_id = $config_manager->getValueAsString('watermark_photo_id');
		if ($this->watermark_photo_id === '') {
			// Watermark photo ID is not set, we cannot watermark
			Log::error('Watermark is enabled but photo id is not set.');

			return false;
		}

		return true;
	}

	/**
	 * Check and load the watermark image.
	 *
	 * @return bool true if watermark image is loaded, false if it is not usable
	 */
	private function check_watermark_image(): bool
	{
		if ($this->size_variant_watermark !== null && $this->size_variant_watermark->photo_id === $this->watermark_photo_id) {
			return true;
		}

		// If the id is different or the image is null, we fetch the image.
		$watermark = SizeVariant::query()
			->where('photo_id', '=', $this->watermark_photo_id)
			->where('type', '=', SizeVariantType::ORIGINAL->value)
			->first();

		if ($watermark === null) {
			// If no watermark is found, we cannot watermark
			Log::error('Watermark original size_variant not found for id:' . $this->watermark_photo_id);

			return false;
		}

		$this->size_variant_watermark = $watermark;

		return true;
	}

	/**
	 * Check if we can watermark images.
	 *
	 * @return bool true if watermarking is possible
	 */
	public function can_watermark(): bool
	{
		return $this->is_watermark_enabled() && $this->check_watermark_image();
	}

	/**
	 * Return the path to the watermarked image if enabled and available.
	 * Returns the appropriate path to the image, either watermarked or original,
	 * based on configuration settings and user authentication status.
	 *
	 * @param SizeVariant $size_variant The size variant to get the path for
	 *
	 * @return string The appropriate path to the image
	 *
	 * @throws LycheeLogicException If trying to get a watermark path for a placeholder
	 */
	public function get_path(SizeVariant $size_variant): string
	{
		// Guard against placeholders and RAW files which cannot be watermarked
		if ($size_variant->type === SizeVariantType::PLACEHOLDER) {
			throw new LycheeLogicException('Cannot get watermark path for placeholder.');
		}

		if ($size_variant->type === SizeVariantType::RAW) {
			throw new LycheeLogicException('Cannot get watermark path for raw file.');
		}

		// Early return conditions where we should use the original path
		if (!self::should_use_watermarked_path($size_variant)) {
			return $size_variant->short_path;
		}

		$config_manager = resolve(ConfigManager::class);

		// Apply watermark for public users if enabled
		if ($config_manager->getValueAsBool('watermark_public') && Auth::guest()) {
			return $size_variant->short_path_watermarked;
		}

		// Apply watermark for authenticated users if enabled
		if ($config_manager->getValueAsBool('watermark_logged_in_users_enabled') && Auth::check()) {
			return $size_variant->short_path_watermarked;
		}

		// Fallback to original path if no condition matches
		return $size_variant->short_path;
	}

	/**
	 * Determines if a watermarked path should be used based on configuration
	 * and the properties of the size variant.
	 *
	 * @param SizeVariant $size_variant The size variant to check
	 *
	 * @return bool True if watermarked path could be used, false otherwise
	 */
	private function should_use_watermarked_path(SizeVariant $size_variant): bool
	{
		$config_manager = resolve(ConfigManager::class);
		// Watermarking must be enabled globally
		if (!$config_manager->getValueAsBool('watermark_enabled')) {
			return false;
		}

		// Watermarked path must exist
		if ($size_variant->short_path_watermarked === null) {
			return false;
		}

		// Special case for original size variants
		if ($size_variant->type === SizeVariantType::ORIGINAL && !$config_manager->getValueAsBool('watermark_original')) {
			return false;
		}

		return true;
	}

	/**
	 * @param SizeVariant $size_variant unencoded placeholder size variant
	 */
	public function do(SizeVariant $size_variant): void
	{
		if (!$this->is_watermark_enabled()) {
			return;
		}

		if (!$this->check_watermark_image()) {
			return;
		}

		$config_manager = resolve(ConfigManager::class);
		if ($size_variant->type === SizeVariantType::PLACEHOLDER ||
			$size_variant->type === SizeVariantType::RAW ||
			($size_variant->type === SizeVariantType::ORIGINAL && !$config_manager->getValueAsBool('watermark_original'))) {
			return;
		}

		try {
			$size_variant_handler = app(ImagickHandler::class);
			$size_variant_handler->load($size_variant->getFile());

			$watermark_handler = app(ImagickHandler::class);
			$watermark_handler->load($this->size_variant_watermark->getFile());

			/** @var ImageDimension $sv_dimentions */
			$sv_dimentions = $size_variant_handler->getDimensions();

			$calculator = new CoordinateCalculator();
			// resize the watermark
			$scaled_dimentions = $calculator->apply_scaling($sv_dimentions);
			$watermark_handler = $watermark_handler->cloneAndScale($scaled_dimentions);
			$watermark_dimentions = $watermark_handler->getDimensions();

			$watermark_handler = $watermark_handler->cloneAndChangeOpacity($calculator->get_opacity());

			// calculate the position
			$position = $calculator->get_coordinates($sv_dimentions, $watermark_dimentions);

			// apply shift
			$position = $calculator->apply_shift($sv_dimentions, $position);

			$size_variant_handler = $size_variant_handler->cloneAndCompose($watermark_handler, $position->width, $position->height);
			$this->naming_strategy->setFromSizeVariant($size_variant);

			$watermarked_file = $this->naming_strategy->createFile($size_variant->type, new CreateSizeVariantFlags(
				is_backup: false,
				is_watermark: true,
				disk: $size_variant->storage_disk,
			));
			$size_variant_handler->save($watermarked_file);

			$size_variant->short_path_watermarked = $watermarked_file->getRelativePath();
			$size_variant->save();
			// @codeCoverageIgnoreStart
		} catch (MediaFileOperationException $e) {
			// Log the error, skip to next size variant
			Log::error($e->getMessage(), [$e]);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to generate the watermarked image', $e);
		}
		// @codeCoverageIgnoreEnd
	}
}