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

namespace Tests\Unit\Models;

use App\Models\Configs;
use Tests\AbstractTestCase;

/**
 * Validates the configs introduced/re-typed by Feature 071 as they are
 * stored by the migrations (S-071-07, S-071-08).
 */
class SizeVariantFormatConfigSanityTest extends AbstractTestCase
{
	public function testSizeVariantFormat(): void
	{
		/** @var Configs $config */
		$config = Configs::query()->where('key', '=', 'size_variant_format')->firstOrFail();

		self::assertEquals('original', $config->value);
		self::assertEquals('', $config->sanity('original'));
		self::assertEquals('', $config->sanity('jpeg'));
		self::assertEquals('', $config->sanity('webp'));
		self::assertEquals('Error: Wrong property for size_variant_format, expected original or jpeg or webp, got png.', $config->sanity('png'));
	}

	public function testCompressionQuality(): void
	{
		/** @var Configs $config */
		$config = Configs::query()->where('key', '=', 'compression_quality')->firstOrFail();

		self::assertEquals('int:0:100', $config->type_range);
		self::assertEquals('', $config->sanity('0'));
		self::assertEquals('', $config->sanity('80'));
		self::assertEquals('', $config->sanity('100'));
		self::assertEquals('Error: Wrong property for compression_quality, expected an integer between 0 and 100, got 101.', $config->sanity('101'));
		self::assertEquals('Error: Wrong property for compression_quality, expected an integer between 0 and 100, got -1.', $config->sanity('-1'));
	}
}
