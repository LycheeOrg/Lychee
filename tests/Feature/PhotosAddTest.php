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
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosAddTest extends TestCase
{
	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;
	protected bool $hasExifTools;
	protected int $hasExifToolsInit;
	protected bool $hasFFmpeg;
	protected int $hasFFmpegInit;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);

		$this->hasExifToolsInit = (int) Configs::get_value('has_exiftool', 2);
		Configs::set('has_exiftool', '2');
		$this->hasExifTools = Configs::hasExiftool();

		$this->hasFFmpegInit = (int) Configs::get_value('has_ffmpeg', 2);
		Configs::set('has_ffmpeg', '2');
		$this->hasFFmpeg = Configs::hasFFmpeg();

		AccessControl::log_as_id(0);
	}

	public function tearDown(): void
	{
		AccessControl::logout();

		Configs::set('has_exiftool', $this->hasExifToolsInit);
		Configs::set('has_ffmpeg', $this->hasFFmpegInit);

		parent::tearDown();
	}

	public function testNegativeUpload(): void
	{
		$this->photos_tests->wrong_upload();
		$this->photos_tests->wrong_upload2();
	}

	/**
	 * A simple upload of an ordinary photo.
	 *
	 * @return void
	 */
	public function testSimpleUpload(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$response = $this->photos_tests->get($id);
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
			'id' => $id,
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'shutter' => '30 s',
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => 'night',
			'type' => 'image/jpeg',
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
					'filesize' => 21104156,
				],
			],
		]);
	}

	/**
	 * Tests Apple Live Photo upload.
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		$photo_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		);
		$video_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO)
		);
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		static::assertEquals($photo_id, $video_id);
		static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Google Motion Photo upload.
	 *
	 * @return void
	 */
	public function testGoogleMotionPhotoUpload(): void
	{
		if (!$this->hasExifTools || !$this->hasFFmpeg) {
			static::markTestSkipped('Exiftool or FFmpeg is not available. Test Skipped.');
		}

		$photo_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_IMAGE)
		);
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));

		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	public function testImport()
	{
		// save initial value
		$init_config_value = Configs::get_value('import_via_symlink');

		// enable import via symlink option
		Configs::set('import_via_symlink', '1');
		static::assertEquals('1', Configs::get_value('import_via_symlink'));

		$strRecent = Carbon::now()
			->subDays(intval(Configs::get_value('recent_age', '1')))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		$ids_before_import = Photo::query()->select('id')->where($recentFilter)->pluck('id');
		$num_before_import = $ids_before_import->count();

		// upload the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'));

		// check if the file is still there (without symlinks the photo would have been deleted)
		static::assertEquals(true, file_exists('public/uploads/import/night.jpg'));

		$response = $this->albums_tests->get('recent');
		$responseObj = json_decode($response->getContent());
		$ids_after_import = (new BaseCollection($responseObj->photos))->pluck('id');
		static::assertEquals(Photo::query()->where($recentFilter)->count(), $ids_after_import->count());
		$ids_to_delete = $ids_after_import->diff($ids_before_import)->all();
		$this->photos_tests->delete($ids_to_delete);

		static::assertEquals($num_before_import, Photo::query()->where($recentFilter)->count());

		// set back to initial value
		Configs::set('import_via_symlink', $init_config_value);
	}

	/**
	 * Tests a trick video which is falsely identified as `application/octet-stream`.
	 *
	 * @return void
	 */
	public function testTrickyVideoUpload(): void
	{
		if (!$this->hasExifTools || !$this->hasFFmpeg) {
			static::markTestSkipped('Exiftool or FFmpeg is not available. Test Skipped.');
		}

		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GAMING_VIDEO)
		);
		$response = $this->photos_tests->get($id);
		$response->assertOk();
		$response->assertJson([
			'album_id' => null,
			'id' => $id,
			'title' => 'gaming',
			'type' => 'video/mp4',
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
