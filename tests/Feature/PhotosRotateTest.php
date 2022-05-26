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

use App\Facades\AccessControl;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	public const CONFIG_HAS_EXIF_TOOL = 'has_exiftool';
	public const CONFIG_HAS_FFMPEG_TOOL = 'has_ffmpeg';
	public const CONFIG_EDITOR_ENABLED = 'editor_enabled';

	protected PhotosUnitTest $photos_tests;

	protected bool $hasExifTools;
	protected int $hasExifToolsInit;
	protected bool $hasFFmpeg;
	protected int $hasFFmpegInit;
	protected int $editor_enabled_init;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);

		$this->editor_enabled_init = (int) Configs::get_value(self::CONFIG_EDITOR_ENABLED, 0);
		Configs::set(self::CONFIG_EDITOR_ENABLED, 1);

		$this->hasExifToolsInit = (int) Configs::get_value(self::CONFIG_HAS_EXIF_TOOL, 2);
		Configs::set(self::CONFIG_HAS_EXIF_TOOL, '2');
		$this->hasExifTools = Configs::hasExiftool();

		$this->hasFFmpegInit = (int) Configs::get_value(self::CONFIG_HAS_FFMPEG_TOOL, 2);
		Configs::set(self::CONFIG_HAS_FFMPEG_TOOL, '2');
		$this->hasFFmpeg = Configs::hasFFmpeg();

		AccessControl::log_as_id(0);

		// Assert that photo table is empty
		static::assertDatabaseCount('sym_links', 0);
		static::assertDatabaseCount('size_variants', 0);
		static::assertDatabaseCount('photos', 0);
	}

	public function tearDown(): void
	{
		// Clean up remaining stuff from tests
		DB::table('sym_links')->delete();
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();
		self::cleanPublicFolders();

		Configs::set(self::CONFIG_EDITOR_ENABLED, $this->editor_enabled_init);
		Configs::set(self::CONFIG_HAS_EXIF_TOOL, $this->hasExifToolsInit);
		Configs::set(self::CONFIG_HAS_FFMPEG_TOOL, $this->hasFFmpegInit);

		AccessControl::logout();

		parent::tearDown();
	}

	public function testDisabledEditor(): void
	{
		Configs::set(self::CONFIG_EDITOR_ENABLED, 0);
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 412, 'support for rotation disabled by configuration');
	}

	public function testInvalidValues(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate('-1', 1, 422);
		$this->photos_tests->rotate($id, 'asdq', 422, 'The selected direction is invalid');
		$this->photos_tests->rotate($id, '2', 422, 'The selected direction is invalid');
	}

	/**
	 * @return void
	 */
	public function testSimpleRotation(): void
	{
		$response = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
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
		static::assertHasFFmpegOrSkip();

		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 422, 'MediaFileUnsupportedException');
	}

	public function testGoogleMotionPhotoRotation(): void
	{
		static::assertHasExifToolOrSkip();
		static::assertHasFFmpegOrSkip();

		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->rotate($id, 1, 422, 'MediaFileUnsupportedException');
	}

	public function testDuplicatePhotoRotation(): void
	{
		$photoResponse1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photoID1 = $photoResponse1->offsetGet('id');
		$photoResponse2 = $this->photos_tests->duplicate(
			[$photoID1], null
		);
		$photoID2 = $photoResponse2->offsetGet('id');

		static::assertNotEquals($photoID1, $photoID2);

		$photoResponse1->assertJson([
			'size_variants' => [
				'small' => ['width' => 540, 'height' => 360],
				'medium' => ['width' => 1620, 'height' => 1080],
				'original' => ['width' => 6720, 'height' => 4480],
			],
		]);
		$photoResponse2->assertJson([
			'size_variants' => [
				'small' => ['width' => 540, 'height' => 360],
				'medium' => ['width' => 1620, 'height' => 1080],
				'original' => ['width' => 6720, 'height' => 4480],
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

	protected function assertHasExifToolOrSkip(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}
	}

	protected function assertHasFFmpegOrSkip(): void
	{
		if (!$this->hasFFmpeg) {
			static::markTestSkipped('FFmpeg is not available. Test Skipped.');
		}
	}
}
