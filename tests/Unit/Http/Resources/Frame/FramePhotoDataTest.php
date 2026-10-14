<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Http\Resources\Frame;

use App\Http\Resources\Frame\FramePhotoData;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Tests\AbstractTestCase;

/**
 * Feature 088: photo information shown in the Frame info box
 * (FR-088-05, FR-088-07, S-088-09, S-088-10).
 */
class FramePhotoDataTest extends AbstractTestCase
{
	private function photo(): Photo
	{
		$photo = new Photo();
		$photo->type = 'image/jpeg';
		$photo->title = 'Sunset over the bay';
		$photo->focal = '35 mm';
		$photo->aperture = 'f/2.8';
		$photo->shutter = '1/250 s';
		$photo->iso = '100';
		$photo->make = 'Fujifilm';
		$photo->model = 'X-T5';
		$photo->lens = 'XF35mmF1.4 R';
		$photo->taken_at = Carbon::create(2024, 8, 12, 19, 47, 0, 'UTC');

		return $photo;
	}

	public function testEveryValueWhenVisible(): void
	{
		$data = FramePhotoData::fromPhoto($this->photo(), ['Best of', 'Holidays'], is_exif_visible: true, is_title_visible: true, taken_at_format: 'Y-m-d H:i');

		self::assertSame('Sunset over the bay', $data->title);
		self::assertSame(['Best of', 'Holidays'], $data->albums);
		self::assertSame('35 mm', $data->focal);
		self::assertSame('f/2.8', $data->aperture);
		self::assertSame('1/250 s', $data->shutter);
		self::assertSame('ISO 100', $data->iso);
		self::assertSame('Fujifilm', $data->make);
		self::assertSame('X-T5', $data->model);
		self::assertSame('XF35mmF1.4 R', $data->lens);
		self::assertSame('2024-08-12 19:47', $data->taken_at);
	}

	public function testMissingValuesAreNull(): void
	{
		$photo = $this->photo();
		$photo->lens = '';
		$photo->iso = null;
		$photo->make = null;
		$photo->taken_at = null;

		$data = FramePhotoData::fromPhoto($photo, null, is_exif_visible: true, is_title_visible: true, taken_at_format: 'Y-m-d H:i');

		self::assertNull($data->lens);
		self::assertNull($data->iso);
		self::assertNull($data->make);
		self::assertNull($data->taken_at);
		self::assertNull($data->albums);
		self::assertSame('X-T5', $data->model);
		self::assertSame('35 mm', $data->focal);
	}

	public function testExifHiddenKeepsTheCaptureDate(): void
	{
		$data = FramePhotoData::fromPhoto($this->photo(), null, is_exif_visible: false, is_title_visible: true, taken_at_format: 'Y-m-d H:i');

		self::assertNull($data->focal);
		self::assertNull($data->aperture);
		self::assertNull($data->shutter);
		self::assertNull($data->iso);
		self::assertNull($data->make);
		self::assertNull($data->model);
		self::assertNull($data->lens);
		self::assertSame('2024-08-12 19:47', $data->taken_at);
	}

	public function testTitleHiddenOrEmptyIsNull(): void
	{
		$hidden = FramePhotoData::fromPhoto($this->photo(), null, is_exif_visible: true, is_title_visible: false, taken_at_format: 'Y-m-d H:i');
		self::assertNull($hidden->title);

		$photo = $this->photo();
		$photo->title = '';
		$empty = FramePhotoData::fromPhoto($photo, null, is_exif_visible: true, is_title_visible: true, taken_at_format: 'Y-m-d H:i');
		self::assertNull($empty->title);
	}
}
