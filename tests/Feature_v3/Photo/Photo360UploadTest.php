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

namespace Tests\Feature_v3\Photo;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Tests\Constants\TestConstants;
use Tests\Feature_v3\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresExifTool;

/**
 * Feature 082 (FR-082-01, FR-082-03): 360° detection on upload with the
 * exiftool and the Imagick reader.
 */
class Photo360UploadTest extends BaseApiWithDataTest
{
	use RequiresExifTool;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresExifTool();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresExifTool();
		parent::tearDown();
	}

	public function testFullSphereWithExiftool(): void
	{
		$this->assertHasExifToolOrSkip();

		$photo = $this->uploadInto(TestConstants::SAMPLE_FILE_PHOTOSPHERE);

		$this->assertFullSphere($photo);
	}

	public function testFullSphereWithImagick(): void
	{
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, 0);

		$photo = $this->uploadInto(TestConstants::SAMPLE_FILE_PHOTOSPHERE);

		$this->assertFullSphere($photo);
	}

	public function testPartialSphereWithExiftool(): void
	{
		$this->assertHasExifToolOrSkip();

		$photo = $this->uploadInto(TestConstants::SAMPLE_FILE_PHOTOSPHERE_PARTIAL);

		$this->assertPartialSphere($photo);
	}

	public function testPartialSphereWithImagick(): void
	{
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, 0);

		$photo = $this->uploadInto(TestConstants::SAMPLE_FILE_PHOTOSPHERE_PARTIAL);

		$this->assertPartialSphere($photo);
	}

	public function testPlainPhotoIsFlat(): void
	{
		$photo = $this->uploadInto(TestConstants::SAMPLE_FILE_HOCHUFERWEG);

		self::assertFalse($photo->is_360);
		self::assertNull($photo->pano_full_width);
		self::assertNull($photo->pano_crop_left);
	}

	private function uploadInto(string $filename): Photo
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();

		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $album->id);
		$this->assertCreated($response);

		return Photo::query()->whereHas('albums', fn ($q) => $q->where('albums.id', '=', $album->id))->sole();
	}

	private function assertFullSphere(Photo $photo): void
	{
		self::assertTrue($photo->is_360);
		self::assertNull($photo->pano_full_width);
		self::assertNull($photo->pano_full_height);
		self::assertNull($photo->pano_crop_left);
		self::assertNull($photo->pano_crop_top);
	}

	/**
	 * FX-082-02: GPano full 8000×4000, crop 6000×2000 at (1000, 1000),
	 * file 600 px wide → factor 0.1.
	 */
	private function assertPartialSphere(Photo $photo): void
	{
		self::assertTrue($photo->is_360);
		self::assertSame(800, $photo->pano_full_width);
		self::assertSame(400, $photo->pano_full_height);
		self::assertSame(100, $photo->pano_crop_left);
		self::assertSame(100, $photo->pano_crop_top);
	}
}
