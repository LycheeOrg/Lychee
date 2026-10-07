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

use App\Actions\Insights\Helpers\AspectRatioClassifier;
use App\Enum\AspectRatioGroup;
use App\Enum\ImageOrientation;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Covers orientation and aspect-ratio grouping (Feature 085, FR-085-17).
 */
class AspectRatioClassifierTest extends AbstractTestCase
{
	/**
	 * @return array<string,array{0:int,1:int,2:AspectRatioGroup|null,3:ImageOrientation}>
	 */
	public static function sizeProvider(): array
	{
		return [
			'3:2 landscape' => [6000, 4000, AspectRatioGroup::THREE_TWO, ImageOrientation::LANDSCAPE],
			'3:2 portrait' => [4000, 6000, AspectRatioGroup::THREE_TWO, ImageOrientation::PORTRAIT],
			'3:2 off by a pixel' => [500, 333, AspectRatioGroup::THREE_TWO, ImageOrientation::LANDSCAPE],
			'4:3' => [4032, 3024, AspectRatioGroup::FOUR_THREE, ImageOrientation::LANDSCAPE],
			'16:9' => [1920, 1080, AspectRatioGroup::SIXTEEN_NINE, ImageOrientation::LANDSCAPE],
			'5:4' => [5000, 4000, AspectRatioGroup::FIVE_FOUR, ImageOrientation::LANDSCAPE],
			'2:1' => [4000, 2000, AspectRatioGroup::TWO_ONE, ImageOrientation::LANDSCAPE],
			'square' => [3000, 3000, AspectRatioGroup::SQUARE, ImageOrientation::SQUARE],
			'panorama' => [12000, 3000, AspectRatioGroup::PANORAMA, ImageOrientation::LANDSCAPE],
			'other' => [1000, 700, AspectRatioGroup::OTHER, ImageOrientation::LANDSCAPE],
			'zero side' => [0, 1000, null, ImageOrientation::UNKNOWN],
			'nothing' => [0, 0, null, ImageOrientation::UNKNOWN],
		];
	}

	#[DataProvider('sizeProvider')]
	public function testClassify(int $width, int $height, ?AspectRatioGroup $group, ImageOrientation $orientation): void
	{
		self::assertSame($group, AspectRatioClassifier::group($width, $height));
		self::assertSame($orientation, AspectRatioClassifier::orientation($width, $height));
	}
}
