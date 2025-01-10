<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v1\Base;

use App\Models\Configs;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

abstract class BasePhotosRotateTest extends BasePhotoTest
{
	public const CONFIG_EDITOR_ENABLED = 'editor_enabled';

	protected int $editor_enabled_init;

	public function setUp(): void
	{
		parent::setUp();
		$this->editor_enabled_init = Configs::getValueAsInt(self::CONFIG_EDITOR_ENABLED);
		Configs::set(self::CONFIG_EDITOR_ENABLED, 1);
	}

	public function tearDown(): void
	{
		Configs::set(self::CONFIG_EDITOR_ENABLED, $this->editor_enabled_init);
		parent::tearDown();
	}

	public function testDisabledEditor(): void
	{
		Configs::set(self::CONFIG_EDITOR_ENABLED, 0);
		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 412, 'support for rotation disabled by configuration');
	}

	public function testInvalidValues(): void
	{
		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate('-1', 1, 422);
		$this->photos_tests->rotate($id, 2, 422, 'The selected direction is invalid');
	}

	/**
	 * @return void
	 */
	public function testSimpleRotation(): void
	{
		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 540, 'height' => 360],
				'medium' => ['width' => 1620, 'height' => 1080],
				'original' => ['width' => 6720, 'height' => 4480],
			],
		]);

		$response = $this->photos_tests->rotate($response->offsetGet('id'), 1);
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 240, 'height' => 360],
				'medium' => ['width' => 720, 'height' => 1080],
				'original' => ['width' => 4480, 'height' => 6720],
			],
		]);
	}

	public function testVideoRotation(): void
	{
		static::assertHasFFMpegOrSkip();

		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_VIDEO)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 422, 'MediaFileUnsupportedException');
	}

	public function testGoogleMotionPhotoRotation(): void
	{
		static::assertHasExifToolOrSkip();
		static::assertHasFFMpegOrSkip();

		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GMP_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 422, 'MediaFileUnsupportedException');
	}

	public function testDuplicatePhotoRotation(): void
	{
		$photoResponse1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photoID1 = $photoResponse1->offsetGet('id');
		$photoResponse2 = $this->photos_tests->duplicate(
			[$photoID1], null
		);
		$photoID2 = $photoResponse2->json()[0]['id'];

		static::assertNotEquals($photoID1, $photoID2);

		$photoResponse1->assertJson([
			'size_variants' => [
				'small' => ['width' => 540, 'height' => 360],
				'medium' => ['width' => 1620, 'height' => 1080],
				'original' => ['width' => 6720, 'height' => 4480],
			],
		]);
		$photoResponse2->assertJson([
			0 => [
				'size_variants' => [
					'small' => ['width' => 540, 'height' => 360],
					'medium' => ['width' => 1620, 'height' => 1080],
					'original' => ['width' => 6720, 'height' => 4480],
				],
			],
		]);

		$this->photos_tests->rotate($photoID1, 1);
		$photoResponse2New = $this->photos_tests->get($photoID2);
		$photoResponse2New->assertJson([
			'size_variants' => [
				'small' => ['width' => 240, 'height' => 360],
				'medium' => ['width' => 720, 'height' => 1080],
				'original' => ['width' => 4480, 'height' => 6720],
			],
		]);
	}
}
