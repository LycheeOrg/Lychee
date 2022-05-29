<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Base\PhotoTestBase;
use Tests\TestCase;

/**
 * Contains all tests for adding photos to Lychee which involve the image
 * handler.
 *
 * The idea is to inherit this class be real test classes which enable
 * a particular image handler (i.e. Imagick, GD, etc.)
 */
abstract class PhotosAddHandlerTestAbstract extends PhotoTestBase
{
	/**
	 * A simple upload of an ordinary photo to the root album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToRoot(): void
	{
		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		/*
		 * Check some Exif data
		 */
		$taken_at = Carbon::create(
			2019, 6, 1, 1, 28, 25, '+02:00'
		);
		$response->assertJson([
			'album_id' => null,
			'aperture' => 'f/2.8',
			'focal' => '16 mm',
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'shutter' => '30 s',
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => 'night',
			'type' => TestCase::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'small' => [
					'width' => 540,
					'height' => 360,
				],
				'medium' => [
					'width' => 1620,
					'height' => 1080,
				],
				'original' => [
					'width' => 6720,
					'height' => 4480,
					'filesize' => 21106422,
				],
			],
		]);
	}

	/**
	 * Tests auto-orientation with rotation 90° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation90(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_ORIENTATION_90)
		);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 480, 'height' => 360],
				'medium' => ['width' => 1440, 'height' => 1080],
				'original' => ['width' => 2016,	'height' => 1512],
			],
		]);
	}

	/**
	 * Tests auto-orientation with rotation 180° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation180(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_ORIENTATION_180)
		);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 480, 'height' => 360],
				'medium' => ['width' => 1440, 'height' => 1080],
				'original' => ['width' => 2016,	'height' => 1512],
			],
		]);
	}

	/**
	 * Tests auto-orientation with rotation 270° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation270(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_ORIENTATION_270)
		);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 480, 'height' => 360],
				'medium' => ['width' => 1440, 'height' => 1080],
				'original' => ['width' => 2016,	'height' => 1512],
			],
		]);
	}

	/**
	 * Tests auto-orientation with horizontal mirroring.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientationHFlip(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_ORIENTATION_HFLIP)
		);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 480, 'height' => 360],
				'medium' => ['width' => 1440, 'height' => 1080],
				'original' => ['width' => 2016,	'height' => 1512],
			],
		]);
	}

	/**
	 * Tests auto-orientation with vertial mirroring.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientationVFlip(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_ORIENTATION_VFLIP)
		);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'size_variants' => [
				'small' => ['width' => 480, 'height' => 360],
				'medium' => ['width' => 1440, 'height' => 1080],
				'original' => ['width' => 2016,	'height' => 1512],
			],
		]);
	}

	public function testPNGUpload(): void
	{
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_PNG)
		));
		static::assertStringEndsWith('.png', $photo->size_variants->original->url);
	}

	public function testGIFUpload(): void
	{
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GIF)
		));
		static::assertStringEndsWith('.gif', $photo->size_variants->original->url);
	}

	public function testWEBPUpload(): void
	{
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_WEBP)
		));
		static::assertStringEndsWith('.webp', $photo->size_variants->original->url);
	}

	/**
	 * Tests Apple Live Photo upload (photo first, video second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload1(): void
	{
		$this->assertHasExifToolOrSkip();

		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		));
		$video = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO),
			null,
			200
		));
		static::assertEquals($photo->id, $video->id);
		static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $video->live_photo_content_id);
		static::assertStringEndsWith('.mov', $video->live_photo_url);
		static::assertEquals(pathinfo($video->live_photo_url, PATHINFO_DIRNAME), pathinfo($video->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($video->live_photo_url, PATHINFO_FILENAME), pathinfo($video->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Apple Live Photo upload (video first, photo second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload2(): void
	{
		$this->assertHasExifToolOrSkip();

		$video = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO)
		));
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		));
		static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

		// The initially uploaded video should have been deleted
		static::assertEquals(0, DB::table('photos')->where('id', '=', $video->id)->count());
	}

	/**
	 * Tests Google Motion Photo upload.
	 *
	 * @return void
	 */
	public function testGoogleMotionPhotoUpload(): void
	{
		$this->assertHasExifToolOrSkip();
		$this->assertHasFFMpegOrSkip();

		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_IMAGE)
		));

		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Google Motion Photo upload with a file which has a broken
	 * video stream.
	 *
	 * @return void
	 */
	public function testBrokenGoogleMotionPhotoUpload(): void
	{
		$this->assertHasExifToolOrSkip();
		$this->assertHasFFMpegOrSkip();

		$this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_BROKEN_IMAGE),
			null,
			500,
			'MediaFileOperationException'
		);
	}

	/**
	 * Tests a trick video which is falsely identified as `application/octet-stream`.
	 *
	 * @return void
	 */
	public function testTrickyVideoUpload(): void
	{
		$this->assertHasExifToolOrSkip();
		$this->assertHasFFMpegOrSkip();

		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GAMING_VIDEO)
		);
		$response->assertJson([
			'album_id' => null,
			'title' => 'gaming',
			'type' => TestCase::MIME_TYPE_VID_MP4,
			'size_variants' => [
				'thumb' => [
					'width' => 200,
					'height' => 200,
				],
				'thumb2x' => [
					'width' => 400,
					'height' => 400,
				],
				'small' => [
					'width' => 640,
					'height' => 360,
				],
				'small2x' => [
					'width' => 1280,
					'height' => 720,
				],
				'original' => [
					'width' => 1920,
					'height' => 1080,
					'filesize' => 66781184,
				],
			],
		]);
	}
}
