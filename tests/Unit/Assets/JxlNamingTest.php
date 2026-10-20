<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Assets;

use App\Assets\SizeVariantGroupedWithRandomSuffixNamingStrategy;
use App\Enum\SizeVariantType;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Tests\AbstractTestCase;

/**
 * Extensions of generated size variants for JPEG XL (Feature 101, FR-101-03, FR-101-04).
 */
class JxlNamingTest extends AbstractTestCase
{
	private const GENERATED_VARIANTS = [
		SizeVariantType::THUMB,
		SizeVariantType::THUMB2X,
		SizeVariantType::SMALL,
		SizeVariantType::SMALL2X,
		SizeVariantType::MEDIUM,
		SizeVariantType::MEDIUM2X,
	];

	private string $init_format;

	protected function setUp(): void
	{
		parent::setUp();
		$this->init_format = resolve(ConfigManager::class)->getValueAsString('size_variant_format');
	}

	protected function tearDown(): void
	{
		Configs::set('size_variant_format', $this->init_format);
		parent::tearDown();
	}

	/**
	 * S-101-03: `jxl` forces `.jxl` on every generated variant; the original keeps its extension.
	 */
	public function testJxlFormatForcesJxlExtension(): void
	{
		Configs::set('size_variant_format', 'jxl');
		$strategy = $this->strategy('image/jpeg', '.jpg');

		foreach (self::GENERATED_VARIANTS as $variant) {
			self::assertSame('.jxl', $strategy->extensionFor($variant), $variant->name);
		}
		self::assertSame('.jpg', $strategy->extensionFor(SizeVariantType::ORIGINAL));
		self::assertSame('.webp', $strategy->extensionFor(SizeVariantType::PLACEHOLDER));
	}

	/**
	 * S-101-02: under `original`, a JPEG XL original keeps `.jxl` for small/medium, thumbs stay JPEG.
	 */
	public function testOriginalFormatKeepsJxlForSmallAndMedium(): void
	{
		Configs::set('size_variant_format', 'original');
		$strategy = $this->strategy('image/jxl', '.jxl');

		self::assertSame('.jpeg', $strategy->extensionFor(SizeVariantType::THUMB));
		self::assertSame('.jpeg', $strategy->extensionFor(SizeVariantType::THUMB2X));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::SMALL));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::SMALL2X));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::MEDIUM));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::MEDIUM2X));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::ORIGINAL));
	}

	/**
	 * FR-101-03: `webp` still applies to JPEG XL originals.
	 */
	public function testWebpFormatAppliesToJxlOriginal(): void
	{
		Configs::set('size_variant_format', 'webp');
		$strategy = $this->strategy('image/jxl', '.jxl');

		self::assertSame('.webp', $strategy->extensionFor(SizeVariantType::SMALL));
		self::assertSame('.jxl', $strategy->extensionFor(SizeVariantType::ORIGINAL));
	}

	private function strategy(string $mime_type, string $extension): object
	{
		$photo = new Photo();
		$photo->type = $mime_type;

		$strategy = new class() extends SizeVariantGroupedWithRandomSuffixNamingStrategy {
			public function extensionFor(SizeVariantType $size_variant): string
			{
				return $this->generateExtension($size_variant);
			}
		};
		$strategy->setPhoto($photo);
		$strategy->setExtension($extension);

		return $strategy;
	}
}
