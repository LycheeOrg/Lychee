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

namespace Tests\ImageProcessing\Image\Handlers;

use App\Facades\Helpers;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Safe\date;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresExifTool;
use Tests\Traits\RequiresFFMpeg;

/**
 * Contains all tests for adding photos to Lychee which involve the image
 * handler.
 *
 * The idea is to inherit this class by real test classes which enable
 * a particular image handler (i.e. Imagick, GD, etc.)
 */
abstract class BaseImageHandler extends BaseApiWithDataTest
{
	use RequiresExifTool;
	use RequiresFFMpeg;

	protected function uploadImage(string $filename)
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $this->album5->id);
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($response);

		return $response;
	}

	/**
	 * A simple upload of an ordinary photo to the root album.
	 *
	 * @return void
	 */
	public function testSimpleUpload(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		/*
		 * Check some Exif data
		 */
		/** @var Carbon $taken_at */
		$taken_at = Carbon::create(
			2019, 6, 1, 1, 28, 25, '+02:00'
		);
		$photo = $response->json('photos.0');
		self::assertEquals('2.8', $photo['preformatted']['aperture']);
		self::assertEquals('16 mm', $photo['preformatted']['focal']);
		self::assertEquals('ISO 1250', $photo['preformatted']['iso']);
		self::assertEquals('EF16-35mm f/2.8L USM', $photo['preformatted']['lens']);
		self::assertEquals('Canon', $photo['preformatted']['make']);
		self::assertEquals('Canon EOS R', $photo['preformatted']['model']);
		self::assertEquals('30 sec', $photo['preformatted']['shutter']);
		self::assertEquals($taken_at->format('Y-m-d\TH:i:sP'), $photo['taken_at']);
		self::assertEquals($taken_at->getTimezone()->getName(), $photo['taken_at_orig_tz']);
		self::assertEquals('tests/Samples/night.jpg', $photo['title']);
		self::assertEquals(TestConstants::MIME_TYPE_IMG_JPEG, $photo['type']);
		self::assertEquals(200, $photo['size_variants']['thumb']['width']);
		self::assertEquals(200, $photo['size_variants']['thumb']['height']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['width']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['height']);
		self::assertEquals(540, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1080, $photo['size_variants']['small2x']['width']);
		self::assertEquals(720, $photo['size_variants']['small2x']['height']);
		self::assertEquals(1620, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(3240, $photo['size_variants']['medium2x']['width']);
		self::assertEquals(2160, $photo['size_variants']['medium2x']['height']);
		self::assertEquals(6720, $photo['size_variants']['original']['width']);
		self::assertEquals(4480, $photo['size_variants']['original']['height']);
		self::assertEquals('20.13 MB', $photo['size_variants']['original']['filesize']);
	}

	/**
	 * Tests that a placeholder for the source image was encoded.
	 *
	 * @return void
	 */
	public function testUploadWithPlaceholder(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$init_config_value1 = $config_manager->getValue('low_quality_image_placeholder');
		Configs::set('low_quality_image_placeholder', '1');
		static::assertEquals('1', $config_manager->getValue('low_quality_image_placeholder'));

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$photo = $response->json('photos.0');
		self::assertEquals(16, $photo['size_variants']['placeholder']['width']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['height']);
		$responseContent = $response->getContent();
		if ($responseContent !== false) {
			// check for the file signature in the decoded base64 data.
			self::assertStringContainsString('WEBPVP8', \Safe\base64_decode($photo['size_variants']['placeholder']['url']));
			self::assertLessThan(190, Helpers::convertSize($photo['size_variants']['placeholder']['filesize']));
		}

		Configs::set('low_quality_image_placeholder', $init_config_value1);
	}

	/**
	 * Tests auto-orientation with rotation 90° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation90(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_ORIENTATION_90);

		/*
		 * Check some Exif data
		 */
		$photo = $response->json('photos.0');
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
	}

	/**
	 * Tests auto-orientation with rotation 180° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation180(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_ORIENTATION_180);

		/*
		 * Check some Exif data
		 */
		$photo = $response->json('photos.0');
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
	}

	/**
	 * Tests auto-orientation with rotation 270° CW.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientation270(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_ORIENTATION_270);

		/*
		 * Check some Exif data
		 */
		$photo = $response->json('photos.0');
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
	}

	/**
	 * Tests auto-orientation with horizontal mirroring.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientationHFlip(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_ORIENTATION_HFLIP);

		/*
		 * Check some Exif data
		 */
		$photo = $response->json('photos.0');
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
	}

	/**
	 * Tests auto-orientation with vertial mirroring.
	 *
	 * @return void
	 */
	public function testUploadWithAutoOrientationVFlip(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_ORIENTATION_VFLIP);

		/*
		 * Check some Exif data
		 */
		$photo = $response->json('photos.0');
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
	}

	public function testPNGUpload(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PNG);
		self::assertStringEndsWith('.png', $response->json('photos.0.size_variants.original.url'));
	}

	/**
	 * Generated size variants (all but original/raw/placeholder).
	 */
	private const GENERATED_VARIANTS = ['thumb', 'thumb2x', 'small', 'small2x', 'medium', 'medium2x'];

	/**
	 * Uploads a file with the given size variant format/quality and returns the photo.
	 * `small_max_height` is lowered so that the small 400x300 PNG sample also gets a small variant.
	 *
	 * @return array<string,mixed>
	 */
	private function uploadWithFormat(string $filename, string $format, int $quality): array
	{
		$config_manager = resolve(ConfigManager::class);
		$init_format = $config_manager->getValueAsString('size_variant_format');
		$init_quality = $config_manager->getValueAsString('compression_quality');
		$init_small_height = $config_manager->getValueAsString('small_max_height');
		try {
			Configs::set('size_variant_format', $format);
			Configs::set('compression_quality', $quality);
			Configs::set('small_max_height', 100);

			return $this->uploadImage($filename)->json('photos.0');
		} finally {
			Configs::set('size_variant_format', $init_format);
			Configs::set('compression_quality', $init_quality);
			Configs::set('small_max_height', $init_small_height);
		}
	}

	/**
	 * @param array<string,mixed> $photo
	 *
	 * @return array<string,string> variant name => content of the generated file
	 */
	private function generatedVariantFiles(array $photo): array
	{
		$files = [];
		foreach (self::GENERATED_VARIANTS as $variant) {
			if ($photo['size_variants'][$variant] !== null) {
				$files[$variant] = file_get_contents(public_path($this->dropUrlPrefix($photo['size_variants'][$variant]['url'])));
			}
		}
		self::assertArrayHasKey('thumb', $files);
		self::assertArrayHasKey('small', $files);

		return $files;
	}

	/**
	 * S-071-01, S-071-02: default format keeps the pre-feature extensions,
	 * and each file's content matches its extension.
	 */
	public function testSizeVariantFormatOriginalKeepsExtensions(): void
	{
		$photo = $this->uploadWithFormat(TestConstants::SAMPLE_FILE_PNG, 'original', 90);
		$files = $this->generatedVariantFiles($photo);

		self::assertStringEndsWith('.png', $photo['size_variants']['original']['url']);
		self::assertStringEndsWith('.jpeg', $photo['size_variants']['thumb']['url']);
		self::assertEquals("\xFF\xD8", substr($files['thumb'], 0, 2));
		self::assertStringEndsWith('.png', $photo['size_variants']['small']['url']);
		self::assertEquals("\x89PNG", substr($files['small'], 0, 4));
	}

	/**
	 * S-071-05: jpeg format forces .jpeg on small/medium of a PNG original.
	 */
	public function testSizeVariantFormatJpegFromPng(): void
	{
		$photo = $this->uploadWithFormat(TestConstants::SAMPLE_FILE_PNG, 'jpeg', 90);

		self::assertStringEndsWith('.png', $photo['size_variants']['original']['url']);
		foreach ($this->generatedVariantFiles($photo) as $variant => $content) {
			self::assertStringEndsWith('.jpeg', $photo['size_variants'][$variant]['url'], $variant);
			self::assertEquals("\xFF\xD8", substr($content, 0, 2), $variant);
		}
	}

	/**
	 * S-071-03: webp format with lossy quality writes lossy WebP for every generated variant.
	 */
	public function testSizeVariantFormatWebpLossy(): void
	{
		$photo = $this->uploadWithFormat(TestConstants::SAMPLE_FILE_ORIENTATION_90, 'webp', 80);

		self::assertStringEndsWith('.jpg', $photo['size_variants']['original']['url']);
		foreach ($this->generatedVariantFiles($photo) as $variant => $content) {
			self::assertStringEndsWith('.webp', $photo['size_variants'][$variant]['url'], $variant);
			$chunks = $this->webpChunks($content);
			self::assertContains('VP8 ', $chunks, $variant);
			self::assertNotContains('VP8L', $chunks, $variant);
		}
	}

	/**
	 * Returns the FourCC codes of the top-level chunks of a WebP (RIFF) file.
	 * `VP8 ` is lossy image data, `VP8L` lossless; an optional `VP8X` header may precede them.
	 *
	 * @return string[]
	 */
	private function webpChunks(string $content): array
	{
		self::assertEquals('RIFF', substr($content, 0, 4));
		self::assertEquals('WEBP', substr($content, 8, 4));
		$chunks = [];
		$offset = 12;
		while ($offset + 8 <= strlen($content)) {
			$chunks[] = substr($content, $offset, 4);
			$size = \Safe\unpack('V', substr($content, $offset + 4, 4))[1];
			$offset += 8 + $size + ($size % 2);
		}

		return $chunks;
	}

	/**
	 * S-071-04: webp format with quality 0 writes lossless WebP.
	 */
	public function testSizeVariantFormatWebpLossless(): void
	{
		$photo = $this->uploadWithFormat(TestConstants::SAMPLE_FILE_PNG, 'webp', 0);

		foreach ($this->generatedVariantFiles($photo) as $variant => $content) {
			self::assertStringEndsWith('.webp', $photo['size_variants'][$variant]['url'], $variant);
			self::assertContains('VP8L', $this->webpChunks($content), $variant);
		}
	}

	/**
	 * S-071-06: quality 0 on a format without a lossless mode still writes a valid JPEG,
	 * including the auto-rotated original which goes through the same save path.
	 */
	public function testSizeVariantFormatJpegLosslessClamps(): void
	{
		$photo = $this->uploadWithFormat(TestConstants::SAMPLE_FILE_ORIENTATION_90, 'jpeg', 0);

		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
		foreach ($this->generatedVariantFiles($photo) as $variant => $content) {
			self::assertStringEndsWith('.jpeg', $photo['size_variants'][$variant]['url'], $variant);
			self::assertEquals("\xFF\xD8", substr($content, 0, 2), $variant);
		}
	}

	public function testGIFUpload(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_GIF);
		self::assertStringEndsWith('.gif', $response->json('photos.0.size_variants.original.url'));
	}

	public function testWEBPUpload(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_WEBP);
		self::assertStringEndsWith('.webp', $response->json('photos.0.size_variants.original.url'));
	}

	/**
	 * Tests Apple Live Photo upload (photo first, video second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload1(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TRAIN_IMAGE);
		$id = $response->json('photos.0.id');

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TRAIN_VIDEO);
		$photo = $response->json('photos.0');
		$id2 = $response->json('photos.0.id');

		self::assertEquals($id, $id2);
		self::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo['live_photo_content_id']);
		self::assertStringEndsWith('.mov', $photo['live_photo_url']);

		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_DIRNAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_DIRNAME));
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_FILENAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_FILENAME));
	}

	/**
	 * Tests Apple Live Photo upload (video first, photo second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload2(): void
	{
		$this->assertHasExifToolOrSkip();

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TRAIN_VIDEO);
		$id = $response->json('photos.0.id');

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TRAIN_IMAGE);
		$photo = $response->json('photos.0');
		$id2 = $response->json('photos.0.id');

		self::assertNotEquals($id, $id2);
		self::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo['live_photo_content_id']);
		self::assertStringEndsWith('.mov', $photo['live_photo_url']);

		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_DIRNAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_DIRNAME));
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_FILENAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_FILENAME));

		// The initially uploaded video should have been deleted
		self::assertEquals(0, DB::table('photos')->where('id', '=', $id)->count());
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

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_GMP_IMAGE);
		$photo = $response->json('photos.0');

		self::assertStringEndsWith('.mov', $photo['live_photo_url']);
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_DIRNAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_DIRNAME));
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_FILENAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_FILENAME));
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

		file_put_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log'), '');

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_GMP_BROKEN_IMAGE);
		$photo = $response->json('photos.0');

		// Size variants are generated, because they are extracted from the
		// still part of the GMP, not the video part.
		self::assertEquals(TestConstants::SAMPLE_FILE_GMP_BROKEN_IMAGE, $photo['title']);
		self::assertEquals(TestConstants::MIME_TYPE_IMG_JPEG, $photo['type']);
		self::assertEquals(2016, $photo['size_variants']['original']['width']);
		self::assertEquals(1512, $photo['size_variants']['original']['height']);
		self::assertEquals(1440, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(960, $photo['size_variants']['small2x']['width']);
		self::assertEquals(720, $photo['size_variants']['small2x']['height']);
		self::assertEquals(480, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['width']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['height']);
		self::assertEquals(200, $photo['size_variants']['thumb']['width']);
		self::assertEquals(200, $photo['size_variants']['thumb']['height']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['width']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['height']);
		self::assertEquals(null, $photo['live_photo_url']);
		self::assertNotEmpty(file_get_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log')));
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

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_GAMING_VIDEO);
		$photo = $response->json('photos.0');

		self::assertEquals(TestConstants::SAMPLE_FILE_GAMING_VIDEO, $photo['title']);
		self::assertEquals(TestConstants::MIME_TYPE_VID_MP4, $photo['type']);
		self::assertEquals(1920, $photo['size_variants']['original']['width']);
		self::assertEquals(1080, $photo['size_variants']['original']['height']);
		self::assertEquals('63.69 MB', $photo['size_variants']['original']['filesize']);
		self::assertEquals(1280, $photo['size_variants']['small2x']['width']);
		self::assertEquals(720, $photo['size_variants']['small2x']['height']);
		self::assertEquals(640, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['width']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['height']);
		self::assertEquals(200, $photo['size_variants']['thumb']['width']);
		self::assertEquals(200, $photo['size_variants']['thumb']['height']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['width']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['height']);
	}

	/**
	 * Tests video upload without ffmpeg or exiftool.
	 *
	 * @return void
	 */
	public function testVideoUploadWithoutFFmpeg(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$hasExifTool = $config_manager->getValueAsInt(TestConstants::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, 0);

		$hasFFMpeg = $config_manager->getValueAsInt(TestConstants::CONFIG_HAS_FFMPEG);
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, 0);

		file_put_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log'), '');

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_GAMING_VIDEO);
		$photo = $response->json('photos.0');

		self::assertEquals(TestConstants::SAMPLE_FILE_GAMING_VIDEO, $photo['title']);
		self::assertEquals(TestConstants::MIME_TYPE_VID_MP4, $photo['type']);
		self::assertEquals(0, $photo['size_variants']['original']['width']);
		self::assertEquals(0, $photo['size_variants']['original']['height']);
		self::assertEquals('63.69 MB', $photo['size_variants']['original']['filesize']);
		self::assertEquals(null, $photo['size_variants']['small2x']);
		self::assertEquals(null, $photo['size_variants']['small']);
		self::assertEquals(null, $photo['size_variants']['thumb2x']);
		self::assertEquals(null, $photo['size_variants']['thumb']);
		self::assertEquals(null, $photo['size_variants']['placeholder']);
		self::assertEquals(null, $photo['size_variants']['medium2x']);
		self::assertEquals(null, $photo['size_variants']['medium']);

		// In the test suite we cannot really ensure that FFMpeg is not
		// available, because the executable is still part of the test
		// environment.
		// Hence, we can only disable it (see above), but cannot be sure
		// that it isn't called accidentally.
		// As a second-best approach, we check at least for the existence
		// of an error message in the log.
		self::assertNotEmpty(file_get_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log')));

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
		$config_manager = resolve(ConfigManager::class);
		$hasExifTool = $config_manager->getValueAsBool(TestConstants::CONFIG_HAS_EXIF_TOOL);
		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, false);

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_UNDEFINED_EXIF_TAG);
		$photo = $response->json('photos.0');

		self::assertEquals('10.0', $photo['preformatted']['aperture']);
		self::assertEquals('70 mm', $photo['preformatted']['focal']);
		self::assertEquals('ISO 100', $photo['preformatted']['iso']);
		self::assertEquals('17-70mm F2.8-4 DC MACRO OS HSM | Contemporary 013', $photo['preformatted']['lens']);
		self::assertEquals('Canon', $photo['preformatted']['make']);
		self::assertEquals('Canon EOS 100D', $photo['preformatted']['model']);
		self::assertEquals('1/250 sec', $photo['preformatted']['shutter']);
		self::assertEquals(TestConstants::MIME_TYPE_IMG_JPEG, $photo['type']);
		self::assertEquals(3059, $photo['size_variants']['original']['width']);
		self::assertEquals(2083, $photo['size_variants']['original']['height']);
		self::assertEquals('1.65 MB', $photo['size_variants']['original']['filesize']);
		self::assertEquals(529, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(1586, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['width']);
		self::assertEquals(16, $photo['size_variants']['placeholder']['height']);
		self::assertEquals(200, $photo['size_variants']['thumb']['width']);
		self::assertEquals(200, $photo['size_variants']['thumb']['height']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['width']);
		self::assertEquals(400, $photo['size_variants']['thumb2x']['height']);
		self::assertEquals(1057, $photo['size_variants']['small2x']['width']);
		self::assertEquals(720, $photo['size_variants']['small2x']['height']);
		self::assertEquals(null, $photo['size_variants']['medium2x']);

		Configs::set(TestConstants::CONFIG_HAS_EXIF_TOOL, $hasExifTool);
	}

	public function testUploadMultibyteTitle(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_SUNSET_IMAGE);
		$photo = $response->json('photos.0');

		self::assertEquals('tests/Samples/fin de journée.jpg', $photo['title']);
		self::assertEquals('8.0', $photo['preformatted']['aperture']);
		self::assertEquals('200 mm', $photo['preformatted']['focal']);
		self::assertEquals('ISO 400', $photo['preformatted']['iso']);
		self::assertEquals('EF70-200mm f/2.8L IS USM', $photo['preformatted']['lens']);
		self::assertEquals('Canon', $photo['preformatted']['make']);
		self::assertEquals('Canon EOS R5', $photo['preformatted']['model']);
		self::assertEquals('1/320 sec', $photo['preformatted']['shutter']);
		self::assertEquals(TestConstants::MIME_TYPE_IMG_JPEG, $photo['type']);
		self::assertEquals(914, $photo['size_variants']['original']['width']);
		self::assertEquals(1625, $photo['size_variants']['original']['height']);
		self::assertEquals('264.01 KB', $photo['size_variants']['original']['filesize']);
		self::assertEquals(202, $photo['size_variants']['small']['width']);
		self::assertEquals(360, $photo['size_variants']['small']['height']);
		self::assertEquals(607, $photo['size_variants']['medium']['width']);
		self::assertEquals(1080, $photo['size_variants']['medium']['height']);
	}

	public function testUploadMultibyteTitleWithoutExifTool(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$hasExifTool = $config_manager->getValueAsBool(TestConstants::CONFIG_HAS_EXIF_TOOL);
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
		$config_manager = resolve(ConfigManager::class);
		$useLastModifiedDate = $config_manager->getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_WITHOUT_EXIF);
		$photo = $response->json('photos.0');

		self::assertEquals('2023-03-14T20:05:03+00:00', $photo['taken_at']);
		self::assertEquals('+00:00', $photo['taken_at_orig_tz']);

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
		$config_manager = resolve(ConfigManager::class);
		$useLastModifiedDate = $config_manager->getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_WITHOUT_EXIF, album_id: $this->album5->id, file_last_modified_time: 0);
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$photo = $response->json('photos.0');

		self::assertEquals('1970-01-01T00:00:00+00:00', $photo['taken_at']);
		self::assertEquals('+00:00', $photo['taken_at_orig_tz']);

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
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_WITHOUT_EXIF);
		$photo = $response->json('photos.0');

		self::assertEquals(null, $photo['taken_at']);
		self::assertEquals(null, $photo['taken_at_orig_tz']);
	}

	/**
	 * Test the upload of a photo with Exif when the use of file last modified time is enabled.
	 * Expected result is that import succeeds and taken at is set to the value from Exif.
	 *
	 * @return void
	 */
	public function testTakenAtForPhotoUploadWithExif(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$useLastModifiedDate = $config_manager->getValueAsBool(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF);
		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, true);

		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$photo = $response->json('photos.0');

		self::assertEquals('2019-06-01T01:28:25+02:00', $photo['taken_at']);
		self::assertEquals('+02:00', $photo['taken_at_orig_tz']);

		Configs::set(TestConstants::CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF, $useLastModifiedDate);
	}
}