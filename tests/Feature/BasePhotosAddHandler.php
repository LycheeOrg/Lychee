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

use App\Models\Configs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Safe\date;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Tests\AbstractTestCase;
use Tests\Feature\Base\BasePhotoTest;
use Tests\Feature\Constants\TestConstants;

/**
 * Contains all tests for adding photos to Lychee which involve the image
 * handler.
 *
 * The idea is to inherit this class by real test classes which enable
 * a particular image handler (i.e. Imagick, GD, etc.)
 */
abstract class BasePhotosAddHandler extends BasePhotoTest
{
	/**
	 * A simple upload of an ordinary photo to the root album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToRoot(): void
	{
		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		/*
		 * Check some Exif data
		 */
		/** @var Carbon $taken_at */
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
			'taken_at' => $taken_at->format('Y-m-d\TH:i:sP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => 'night',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'thumb' => ['width' => 200, 'height' => 200],
				'thumb2x' => ['width' => 400, 'height' => 400],
				'small' => ['width' => 540,	'height' => 360],
				'small2x' => ['width' => 1080,	'height' => 720],
				'medium' => ['width' => 1620, 'height' => 1080],
				'medium2x' => ['width' => 3240, 'height' => 2160],
				'original' => ['width' => 6720,	'height' => 4480, 'filesize' => 21106422],
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ORIENTATION_90)
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ORIENTATION_180)
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ORIENTATION_270)
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ORIENTATION_HFLIP)
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ORIENTATION_VFLIP)
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
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_PNG)
		));
		$this->assertStringEndsWith('.png', $photo->size_variants->original->url);
	}

	public function testGIFUpload(): void
	{
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GIF)
		));
		$this->assertStringEndsWith('.gif', $photo->size_variants->original->url);
	}

	public function testWEBPUpload(): void
	{
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_WEBP)
		));
		$this->assertStringEndsWith('.webp', $photo->size_variants->original->url);
	}

	/**
	 * Tests Apple Live Photo upload (photo first, video second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload1(): void
	{
		$this->assertHasExifToolOrSkip();

		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE)
		));
		/** @var \App\Models\Photo $video */
		$video = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_VIDEO),
			null,
			200
		));
		$this->assertEquals($photo->id, $video->id);
		$this->assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $video->live_photo_content_id);
		$this->assertStringEndsWith('.mov', $video->live_photo_url);
		$this->assertEquals(pathinfo($video->live_photo_url, PATHINFO_DIRNAME), pathinfo($video->size_variants->original->url, PATHINFO_DIRNAME));
		$this->assertEquals(pathinfo($video->live_photo_url, PATHINFO_FILENAME), pathinfo($video->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Apple Live Photo upload (video first, photo second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload2(): void
	{
		$this->assertHasExifToolOrSkip();

		/** @var \App\Models\Photo $video */
		$video = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_VIDEO)
		));
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE),
			null,
			200 // associated image to video.
		));
		$this->assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		$this->assertStringEndsWith('.mov', $photo->live_photo_url);
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

		// The initially uploaded video should have been deleted
		$this->assertEquals(0, DB::table('photos')->where('id', '=', $video->id)->count());
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

		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GMP_IMAGE)
		));

		$this->assertStringEndsWith('.mov', $photo->live_photo_url);
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Google Motion Photo upload with a file which has a broken
	 * video stream.
	 *
	 * We still expect the still part of the photo to be generated, but
	 * the photo won't be recognized as a Google Motion Photo and the
	 * video part is expected to be missing.
	 *
	 * This is in line with our best effort approach.
	 *
	 * Moreover, the logs should contain an error message, telling the user
	 * what went wrong.
	 *
	 * @return void
	 */
	public function testBrokenGoogleMotionPhotoUpload(): void
	{
		$this->assertHasExifToolOrSkip();
		$this->assertHasFFMpegOrSkip();

		file_put_contents(storage_path('logs/notice-' . date('Y-m-d') . '.log'), '');

		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GMP_BROKEN_IMAGE)
		);
		// Size variants are generated, because they are extracted from the
		// still part of the GMP, not the video part.
		$response->assertJson([
			'album_id' => null,
			'title' => 'google_motion_photo_broken',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'original' => ['width' => 2016, 'height' => 1512],
				'medium2x' => null,
				'medium' => ['width' => 1440, 'height' => 1080],
				'small2x' => ['width' => 960, 'height' => 720],
				'small' => ['width' => 480, 'height' => 360],
				'thumb2x' => ['width' => 400, 'height' => 400],
				'thumb' => ['width' => 200,	'height' => 200],
			],
			'live_photo_url' => null,
		]);

		self::assertNotEmpty(file_get_contents(storage_path('logs/notice-' . date('Y-m-d') . '.log')));
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GAMING_VIDEO)
		);
		$response->assertJson([
			'album_id' => null,
			'title' => 'gaming',
			'type' => TestConstants::MIME_TYPE_VID_MP4,
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

	/**
	 * Tests video upload without ffmpeg or exiftool.
	 *
	 * @return void
	 */
	public function testVideoUploadWithoutFFmpeg(): void
	{
		$hasExifTool = Configs::getValueAsInt(TestConstants::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, 0);

		$hasFFMpeg = Configs::getValueAsInt(TestConstants::CONFIG_HAS_FFMPEG);
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, 0);

		file_put_contents(storage_path('logs/notice-' . date('Y-m-d') . '.log'), '');

		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GAMING_VIDEO)
		);
		$response->assertJson([
			'album_id' => null,
			'title' => 'gaming',
			'type' => TestConstants::MIME_TYPE_VID_MP4,
			'size_variants' => [
				'original' => [
					'width' => 0,
					'height' => 0,
					'filesize' => 66781184,
				],
				'medium2x' => null,
				'medium' => null,
				'small2x' => null,
				'small' => null,
				'thumb2x' => null,
				'thumb' => null,
			],
		]);

		// In the test suite we cannot really ensure that FFMpeg is not
		// available, because the executable is still part of the test
		// environment.
		// Hence, we can only disable it (see above), but cannot be sure
		// that it isn't called accidentally.
		// As a second-best approach, we check at least for the existence
		// of an error message in the log.
		self::assertNotEmpty(file_get_contents(storage_path('logs/notice-' . date('Y-m-d') . '.log')));

		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, $hasFFMpeg);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, $hasExifTool);
	}

	/**
	 * Tests a photo with an undefined EXIF tag (0x0000).
	 * Expected result is that import proceeds and extract as many EXIF
	 * information as possible.
	 *
	 * @return void
	 */
	public function testPhotoUploadWithUndefinedExifTag(): void
	{
		$hasExifTool = Configs::getValueAsBool(TestConstants::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, false);

		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_UNDEFINED_EXIF_TAG)
		);
		$response->assertJson([
			'album_id' => null,
			'aperture' => 'f/10.0',
			'focal' => '70 mm',
			'iso' => '100',
			'lens' => '17-70mm F2.8-4 DC MACRO OS HSM | Contemporary 013',
			'make' => 'Canon',
			'model' => 'Canon EOS 100D',
			'shutter' => '1/250 s',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'thumb' => ['width' => 200, 'height' => 200],
				'thumb2x' => ['width' => 400, 'height' => 400],
				'small' => ['width' => 529,	'height' => 360],
				'small2x' => ['width' => 1057,	'height' => 720],
				'medium' => ['width' => 1586, 'height' => 1080],
				'medium2x' => null,
				'original' => ['width' => 3059,	'height' => 2083, 'filesize' => 1734545],
			],
		]);

		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, $hasExifTool);
	}

	public function testUploadMultibyteTitle(): void
	{
		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');

		$response = $this->photos_tests->get($id);
		$response->assertJson([
			'album_id' => null,
			'title' => 'fin de journée',
			'description' => null,
			'tags' => [],
			'license' => 'none',
			'is_public' => false,
			'is_starred' => false,
			'iso' => '400',
			'make' => 'Canon',
			'model' => 'Canon EOS R5',
			'lens' => 'EF70-200mm f/2.8L IS USM',
			'aperture' => 'f/8.0',
			'shutter' => '1/320 s',
			'focal' => '200 mm',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'small' => [
					'width' => 202,
					'height' => 360,
				],
				'medium' => [
					'width' => 607,
					'height' => 1080,
				],
				'original' => [
					'width' => 914,
					'height' => 1625,
					'filesize' => 270345,
				],
			],
		]);
	}

	public function testUploadMultibyteTitleWithoutExifTool(): void
	{
		$hasExifTool = Configs::getValueAsBool(TestConstants::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, false);
		$this->testUploadMultibyteTitle();
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, $hasExifTool);
	}

	/**
	 * Test the upload of a photo without Exif when the use of file last modified time is enabled.
	 * Expected result is that import succeeds and taken at property has the correct value.
	 *
	 * @return void
	 */
	public function testTakenAtForPhotoUploadWithoutExif(): void
	{
		$useLastModifiedDate = Configs::getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_WITHOUT_EXIF)
		);
		$response->assertJson([
			'taken_at' => '2023-03-14T20:05:03+00:00',
			'taken_at_orig_tz' => '+00:00',
		]);

		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, $useLastModifiedDate);
	}

	/**
	 * Test the upload of a photo without Exif when the use of file last modified time is enabled and the value is set to 0.
	 * Expected result is that import proceeds and taken at is set to 1st Jan 1970.
	 *
	 * @return void
	 */
	public function testTakenAtForPhotoUploadWithoutExif2(): void
	{
		$useLastModifiedDate = Configs::getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$response = $this->photos_tests->upload(
			file: AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_WITHOUT_EXIF),
			fileLastModifiedTime: 0
		);
		$response->assertJson([
			'taken_at' => '1970-01-01T00:00:00+00:00',
			'taken_at_orig_tz' => '+00:00',
		]);

		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, $useLastModifiedDate);
	}

	/**
	 * Test the upload of a photo without Exif when the use of file last modified time is disabled.
	 * Expected result is that import proceeds and taken at has no value.
	 *
	 * @return void
	 */
	public function testTakenAtForPhotoUploadWithoutExif3(): void
	{
		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_WITHOUT_EXIF)
		);
		$response->assertJson([
			'taken_at' => null,
			'taken_at_orig_tz' => null,
		]);
	}

	/**
	 * Test the upload of a photo with Exif when the use of file last modified time is enabled.
	 * Expected result is that import succeeds and taken at is set to the value from Exif.
	 *
	 * @return void
	 */
	public function testTakenAtForPhotoUploadWithExif(): void
	{
		$useLastModifiedDate = Configs::getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$response->assertJson([
			'taken_at' => '2019-06-01T01:28:25+02:00',
			'taken_at_orig_tz' => '+02:00',
		]);

		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, $useLastModifiedDate);
	}
}
