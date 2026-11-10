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

use App\Actions\Insights\Helpers\DeviceNormaliser;
use App\Enum\DeviceCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Covers Feature 085's device grouping (FR-085-12, S-085-18).
 */
class DeviceNormaliserTest extends AbstractTestCase
{
	/**
	 * @return array<string,array{0:string|null,1:string|null,2:string|null,3:string|null,4:DeviceCategory}>
	 */
	public static function deviceProvider(): array
	{
		return [
			'corporation suffix and model prefix' => ['NIKON CORPORATION', 'NIKON D850', 'Nikon', 'Nikon D850', DeviceCategory::CAMERA],
			'plain maker' => ['Nikon', 'D850', 'Nikon', 'Nikon D850', DeviceCategory::CAMERA],
			'model repeats the maker' => ['Canon', 'Canon EOS R6', 'Canon', 'Canon EOS R6', DeviceCategory::CAMERA],
			'imaging corp' => ['OLYMPUS IMAGING CORP.', 'E-M5', 'Olympus', 'Olympus E-M5', DeviceCategory::CAMERA],
			'om digital solutions' => ['OM Digital Solutions', 'OM-1', 'OM System', 'OM System OM-1', DeviceCategory::CAMERA],
			'leica camera ag' => ['LEICA CAMERA AG', 'LEICA Q2', 'Leica', 'Leica Q2', DeviceCategory::CAMERA],
			'fujifilm' => ['FUJIFILM', 'X-T4', 'Fujifilm', 'Fujifilm X-T4', DeviceCategory::CAMERA],
			'iphone' => ['Apple', 'iPhone 13 Pro', 'Apple', 'Apple iPhone 13 Pro', DeviceCategory::MOBILE],
			'pixel' => ['Google', 'Pixel 7', 'Google', 'Google Pixel 7', DeviceCategory::MOBILE],
			'samsung phone code' => ['samsung', 'SM-G991B', 'Samsung', 'Samsung SM-G991B', DeviceCategory::MOBILE],
			'samsung camera' => ['SAMSUNG', 'NX300', 'Samsung', 'Samsung NX300', DeviceCategory::CAMERA],
			'sony phone' => ['Sony', 'Xperia 1 IV', 'Sony', 'Sony Xperia 1 IV', DeviceCategory::MOBILE],
			'sony camera' => ['SONY', 'ILCE-7M3', 'Sony', 'Sony ILCE-7M3', DeviceCategory::CAMERA],
			'lg electronics' => ['LG Electronics', 'LG-H870', 'LG', 'LG-H870', DeviceCategory::MOBILE],
			'unknown maker keeps its spelling' => [' Acme Optics ', 'Z1', 'Acme Optics', 'Acme Optics Z1', DeviceCategory::OTHER],
			'model only' => [null, 'Mystery Cam', null, 'Mystery Cam', DeviceCategory::OTHER],
			'maker only' => ['Canon', null, 'Canon', 'Canon', DeviceCategory::CAMERA],
			'nothing' => [null, '  ', null, null, DeviceCategory::OTHER],
		];
	}

	#[DataProvider('deviceProvider')]
	public function testNormalise(?string $make, ?string $model, ?string $manufacturer, ?string $name, DeviceCategory $category): void
	{
		$device = (new DeviceNormaliser())->normalise($make, $model);

		self::assertSame($manufacturer, $device->manufacturer);
		self::assertSame($name, $device->name);
		self::assertSame($category, $device->category);
	}

	public function testSameInputReturnsTheSameRecord(): void
	{
		$normaliser = new DeviceNormaliser();

		self::assertSame($normaliser->normalise('Canon', 'EOS R'), $normaliser->normalise('Canon', 'EOS R'));
	}
}
