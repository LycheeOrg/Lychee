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

namespace Tests\Unit\Actions\Insights;

use App\Actions\Insights\Helpers\ExposureParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Covers reading the stored EXIF strings as numbers (FR-085-13, S-085-12).
 */
class ExposureParserTest extends AbstractTestCase
{
	/**
	 * @return array<string,array{0:string|null,1:float|null}>
	 */
	public static function shutterProvider(): array
	{
		return [
			'fraction with unit' => ['1/250 s', 0.004],
			'fraction' => ['1/250', 0.004],
			'decimal denominator' => ['1/2.5 s', 0.4],
			'seconds' => ['2 s', 2.0],
			'decimal seconds' => ['0.5 s', 0.5],
			'quote' => ['30"', 30.0],
			'null' => [null, null],
			'empty' => ['', null],
			'zero denominator' => ['1/0 s', null],
			'zero' => ['0 s', null],
			'garbage' => ['fast', null],
		];
	}

	#[DataProvider('shutterProvider')]
	public function testShutter(?string $stored, ?float $expected): void
	{
		self::assertSame($expected, ExposureParser::shutter($stored));
	}

	/**
	 * @return array<string,array{0:string,1:string|null,2:float|null}>
	 */
	public static function valueProvider(): array
	{
		return [
			'iso' => ['iso', '100', 100.0],
			'iso with prefix' => ['iso', 'ISO 400', 400.0],
			'iso zero' => ['iso', '0', null],
			'iso garbage' => ['iso', 'auto', null],
			'aperture' => ['aperture', 'f/2.8', 2.8],
			'aperture capital' => ['aperture', 'F2', 2.0],
			'aperture bare' => ['aperture', '1.4', 1.4],
			'aperture empty' => ['aperture', '', null],
			'focal with space' => ['focal', '200 mm', 200.0],
			'focal without space' => ['focal', '24.5mm', 24.5],
			'focal bare' => ['focal', '35', 35.0],
			'focal garbage' => ['focal', 'wide', null],
			'duration' => ['duration', '12.5', 12.5],
			'duration null' => ['duration', null, null],
			'duration negative' => ['duration', '-3', null],
		];
	}

	#[DataProvider('valueProvider')]
	public function testValue(string $field, ?string $stored, ?float $expected): void
	{
		$value = match ($field) {
			'iso' => ExposureParser::iso($stored),
			'aperture' => ExposureParser::aperture($stored),
			'focal' => ExposureParser::focal($stored),
			'duration' => ExposureParser::duration($stored),
		};

		self::assertSame($expected, $value);
	}
}
