<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Assets;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Enum\SizeVariantFormat;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\MissingValueException;
use App\Repositories\ConfigManager;

abstract class BaseSizeVariantNamingStrategy extends AbstractSizeVariantNamingStrategy
{
	/**
	 * The file extension which is always used by both "thumb" variants.
	 * If the media file is not a supported photo format (e.g. the media is
	 * a video), then this extension is also used for the small and medium
	 * size variants.
	 */
	public const THUMB_EXTENSION = '.jpeg';

	/**
	 * The file extension which is always used by placeholder variants.
	 */
	public const PLACEHOLDER_EXTENSION = '.webp';

	/**
	 * Returns the file extension incl. the preceding dot.
	 *
	 * @throws MissingValueException
	 * @throws IllegalOrderOfOperationException
	 */
	protected function generateExtension(SizeVariantType $size_variant): string
	{
		return match ($size_variant) {
			SizeVariantType::ORIGINAL, SizeVariantType::RAW => $this->originalExtension(),
			SizeVariantType::PLACEHOLDER => self::PLACEHOLDER_EXTENSION,
			default => $this->generatedExtension($size_variant),
		};
	}

	/**
	 * Extension of thumb, small, medium and their 2x variants.
	 * `size_variant_format` takes precedence over the default rules.
	 *
	 * @throws MissingValueException
	 * @throws IllegalOrderOfOperationException
	 */
	private function generatedExtension(SizeVariantType $size_variant): string
	{
		$size_variant_format = resolve(ConfigManager::class)
			->getValueAsEnum('size_variant_format', SizeVariantFormat::class);

		return match ($size_variant_format) {
			SizeVariantFormat::JPEG => '.jpeg',
			SizeVariantFormat::WEBP => '.webp',
			default => $this->getExtensionForSizeVariant($size_variant),
		};
	}

	/**
	 * Get extention for size variant if not forced.
	 *
	 * @param SizeVariantType $size_variant
	 *
	 * @return string
	 */
	private function getExtensionForSizeVariant(SizeVariantType $size_variant): string
	{
		if (
			in_array($size_variant, [SizeVariantType::THUMB, SizeVariantType::THUMB2X], true) ||
			!$this->photo->isPhoto()
		) {
			return self::THUMB_EXTENSION;
		}

		return $this->originalExtension();
	}

	/**
	 * @throws MissingValueException
	 */
	private function originalExtension(): string
	{
		if ($this->extension === '') {
			// @codeCoverageIgnoreStart
			throw new MissingValueException('extension');
			// @codeCoverageIgnoreEnd
		}

		return $this->extension;
	}
}
