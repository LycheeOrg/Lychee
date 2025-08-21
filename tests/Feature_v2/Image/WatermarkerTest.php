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

namespace Tests\Feature_v2\Image\Handlers;

use App\Image\Watermarker;
use App\Models\AccessPermission;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Tests\Constants\TestConstants;
use Tests\Traits\InteractsWithRaw;
use Tests\Traits\RequireSE;
use Tests\Traits\RequiresImageHandler;

/**
 * Runs the tests of {@link PhotosAddHandlerTestAbstract} with Imagick as image handler.
 */
class WatermarkerTest extends BaseImageHandler
{
	use InteractsWithRaw;
	use RequiresImageHandler;
	use RequireSE;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresImagick();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		$this->tearDownRequiresImageHandler();
		parent::tearDown();
	}

	/**
	 * Test the different configurations.
	 *
	 * @return void
	 */
	public function testWatermarkerInitNoSetting()
	{
		$watermarker = new Watermarker();
		self::assertEquals(false, $watermarker->can_watermark);
	}

	public function testWatermarkerInitWithImagick()
	{
		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		$watermarker = new Watermarker();
		self::assertEquals(false, $watermarker->can_watermark);
	}

	public function testWatermarkerInitWithImagickAndWrongImage()
	{
		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		Configs::set('watermark_photo_id', 'some id');
		$watermarker = new Watermarker();
		self::assertEquals(false, $watermarker->can_watermark);
	}

	public function testWatermarkerInitWithImagickAndImage()
	{
		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		Configs::set('watermark_photo_id', $this->photo1->id);
		$watermarker = new Watermarker();
		self::assertEquals(true, $watermarker->can_watermark);
	}

	/**
	 * Tests uploading of an accepted TIFF.
	 *
	 * As Imagick supports TIFFs, we also expect generated thumbnail.
	 *
	 * @return void
	 */
	public function testWatermarkerWorksNoOriginal(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PNG);
		$photo = $response->json('resource.photos.0');
		$photoId = $photo['id'];

		$response = $this->actingAs($this->admin)->postJson('Photo::move', [
			'from_id' => $this->album5->id,
			'photo_ids' => [$photoId],
			'album_id' => null,
		]);
		$this->assertNoContent($response);

		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		Configs::set('watermark_photo_id', $photoId);
		Configs::set('watermark_photo_id', $photoId);
		Configs::set('watermark_logged_in_users_enabled', true);
		Configs::set('watermark_public', false);
		$watermarker = new Watermarker();
		self::assertEquals(true, $watermarker->can_watermark);

		// Watermarker is enabled, Let's F-ing goooo.
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$photo = $response->json('resource.photos.0');

		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['thumb']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['thumb2x']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['small']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['small2x']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['medium']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo['size_variants']['medium2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['original']['url']);

		// Public can access album 5.
		AccessPermission::factory()->public()->visible()->grants_full_photo()->for_album($this->album5)->create();
		Auth::logout();
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);

		$photo2 = $response->json('resource.photos.0');
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['thumb']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['thumb2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['small']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['small2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['medium']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['medium2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo2['size_variants']['original']['url']);

		// Ensure that the url are different by default.
		self::assertNotEquals(str_replace('.jpeg', '', $photo2['size_variants']['thumb']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['thumb']['url']));
		self::assertNotEquals(str_replace('.jpeg', '', $photo2['size_variants']['thumb2x']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['thumb2x']['url']));
		self::assertNotEquals(str_replace('.jpg', '', $photo2['size_variants']['small']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['small']['url']));
		self::assertNotEquals(str_replace('.jpg', '', $photo2['size_variants']['small2x']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['small2x']['url']));
		self::assertNotEquals(str_replace('.jpg', '', $photo2['size_variants']['medium']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['medium']['url']));
		self::assertNotEquals(str_replace('.jpg', '', $photo2['size_variants']['medium2x']['url']), str_replace('_wmk.jpeg', '', $photo['size_variants']['medium2x']['url']));
	}

	/**
	 * Tests uploading of an accepted TIFF.
	 *
	 * As Imagick supports TIFFs, we also expect generated thumbnail.
	 *
	 * @return void
	 */
	public function testWatermarkerWorksOriginal(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PNG);
		$photo = $response->json('resource.photos.0');
		$photoId = $photo['id'];

		$response = $this->actingAs($this->admin)->postJson('Photo::move', [
			'from_id' => $this->album5->id,
			'photo_ids' => [$photoId],
			'album_id' => null,
		]);
		$this->assertNoContent($response);

		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		Configs::set('watermark_photo_id', $photoId);
		Configs::set('watermark_photo_id', $photoId);
		Configs::set('watermark_original', true);
		Configs::set('watermark_random_path', false);
		Configs::set('watermark_public', true);
		$watermarker = new Watermarker();
		self::assertEquals(true, $watermarker->can_watermark);

		// Watermarker is enabled, Let's F-ing goooo.
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$photo = $response->json('resource.photos.0');

		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['thumb']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['thumb2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['small']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['small2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['medium']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['medium2x']['url']);
		self::assertStringEndsNotWith('_wmk.jpeg', $photo['size_variants']['original']['url']);

		// Public can access album 5.
		AccessPermission::factory()->public()->visible()->grants_full_photo()->for_album($this->album5)->create();
		Auth::logout();
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);

		$photo2 = $response->json('resource.photos.0');
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['thumb']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['thumb2x']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['small']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['small2x']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['medium']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['medium2x']['url']);
		self::assertStringEndsWith('_wmk.jpeg', $photo2['size_variants']['original']['url']);

		// Because we set up `watermark_random_path` to false, the url needs to be the same.
		self::assertEquals(str_replace('.jpeg', '', $photo['size_variants']['thumb']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['thumb']['url']));
		self::assertEquals(str_replace('.jpeg', '', $photo['size_variants']['thumb2x']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['thumb2x']['url']));
		self::assertEquals(str_replace('.jpg', '', $photo['size_variants']['small']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['small']['url']));
		self::assertEquals(str_replace('.jpg', '', $photo['size_variants']['small2x']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['small2x']['url']));
		self::assertEquals(str_replace('.jpg', '', $photo['size_variants']['medium']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['medium']['url']));
		self::assertEquals(str_replace('.jpg', '', $photo['size_variants']['medium2x']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['medium2x']['url']));
		self::assertEquals(str_replace('.jpg', '', $photo['size_variants']['original']['url']), str_replace('_wmk.jpeg', '', $photo2['size_variants']['original']['url']));
	}
}