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

use App\Contracts\SizeVariantNamingStrategy;
use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
		$id = null;

		try {
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

			$this->photos_tests->delete([$id]);
		} finally {
			// Clean-up
			DB::table('size_variants')->whereIn('photo_id', [$id])->delete();
			DB::table('photos')->whereIn('id', [$id])->delete();
		}
	}

	/**
	 * Tests Apple Live Photo upload (photo first, video second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload1(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		$video_id = null;
		$photo_id = null;

		try {
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

			$this->photos_tests->delete([$photo_id]);
		} finally {
			// Clean-up
			DB::table('size_variants')->whereIn('photo_id', [$photo_id, $video_id])->delete();
			DB::table('photos')->whereIn('id', [$photo_id, $video_id])->delete();
		}
	}

	/**
	 * Tests Apple Live Photo upload (video first, photo second).
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload2(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		$video_id = null;
		$photo_id = null;

		try {
			$video_id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO)
			);
			$photo_id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
			);
			$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
			static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
			static::assertStringEndsWith('.mov', $photo->live_photo_url);
			static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
			static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

			// The initially uploaded video should have been deleted
			static::assertEquals(0, DB::table('photos')->where('id', '=', $video_id)->count());

			$this->photos_tests->delete([$photo_id]);
		} finally {
			// Clean-up
			DB::table('size_variants')->whereIn('photo_id', [$photo_id, $video_id])->delete();
			DB::table('photos')->whereIn('id', [$photo_id, $video_id])->delete();
		}
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

		$photo_id = null;

		try {
			$photo_id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_IMAGE)
			);
			$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));

			static::assertStringEndsWith('.mov', $photo->live_photo_url);
			static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
			static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

			$this->photos_tests->delete([$photo_id]);
		} finally {
			// Clean-up
			DB::table('size_variants')->whereIn('photo_id', [$photo_id])->delete();
			DB::table('photos')->whereIn('id', [$photo_id])->delete();
		}
	}

	public function testRecentAlbum(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		$recentAlbumBefore = static::convertJsonToObject($this->albums_tests->get('recent'));
		static::assertCount($ids_before->count(), $recentAlbumBefore->photos);

		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photo_id = $this->photos_tests->get($id)->offsetGet('id');
		$ids_after = static::getRecentPhotoIDs();

		$recentAlbumAfter = static::convertJsonToObject($this->albums_tests->get('recent'));
		static::assertCount($ids_after->count(), $recentAlbumAfter->photos);

		$new_ids = $ids_after->diff($ids_before);
		static::assertCount(1, $new_ids);
		static::assertEquals($photo_id, $new_ids->first());

		$this->photos_tests->delete([$photo_id]);

		$recentAlbum = static::convertJsonToObject($this->albums_tests->get('recent'));
		static::assertEquals($recentAlbumBefore->photos, $recentAlbum->photos);
	}

	public function testImportViaMove(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// import the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, true, false, false);

		// check if the file has been moved
		static::assertEquals(false, file_exists(base_path('public/uploads/import/night.jpg')));

		$ids_after = static::getRecentPhotoIDs();
		$ids_to_delete = $ids_after->diff($ids_before)->all();
		$this->photos_tests->delete($ids_to_delete);
	}

	public function testImportViaCopy(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// import the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false, false);

		// check if the file is still there
		static::assertEquals(true, file_exists(base_path('public/uploads/import/night.jpg')));

		$ids_after = static::getRecentPhotoIDs();
		$ids_to_delete = $ids_after->diff($ids_before)->all();
		$this->photos_tests->delete($ids_to_delete);
	}

	public function testImportViaSymlink(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// import the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false, true);

		// check if the file is still there
		static::assertEquals(true, file_exists(base_path('public/uploads/import/night.jpg')));

		// get the path of the photo object and check whether it is truly a symbolic link
		$ids_after = static::getRecentPhotoIDs();
		$photo_id = $ids_after->diff($ids_before)->first();
		$rel_path = DB::table('size_variants')
			->where('photo_id', '=', $photo_id)
			->where('type', '=', SizeVariant::ORIGINAL)
			->first('short_path')
			->short_path;
		$symlink_path = Storage::disk(SizeVariantNamingStrategy::IMAGE_DISK_NAME)->path($rel_path);
		static::assertEquals(true, is_link($symlink_path));

		$this->photos_tests->delete([$photo_id]);
	}

	public function testImportSkipDuplicateWithResync(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// Upload the photo the first time and remove some information
		// such that there is really something to re-sync
		$first_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		DB::table('photos')
			->where('id', '=', $first_id)
			->update(['make' => null, 'model' => null]);

		$response = $this->photos_tests->get($first_id);
		$response->assertJson([
			'id' => $first_id,
			'make' => null,
			'model' => null,
		]);

		// import the photo a second time and request re-sync
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$report = $this->photos_tests->import(base_path('public/uploads/import/'), null, false, true, false, true);
		static::assertStringNotContainsString('PhotoSkippedException', $report);
		static::assertStringContainsString('PhotoResyncedException', $report);

		// The first photo is expected to have changed
		$response = $this->photos_tests->get($first_id);
		$response->assertJson([
			'id' => $first_id,
			'make' => 'Canon',
			'model' => 'Canon EOS R',
		]);

		// Clean-up
		$ids_after = static::getRecentPhotoIDs();
		$ids_to_delete = $ids_after->diff($ids_before)->all();
		$this->photos_tests->delete($ids_to_delete);
	}

	public function testImportSkipDuplicateWithoutResync(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// Upload the photo the first time
		$this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		// import the photo a second time and skip the duplicate
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false, false, false);
		$report = $this->photos_tests->import(base_path('public/uploads/import/'), null, false, true, false, false);
		static::assertStringContainsString('PhotoSkippedException', $report);
		static::assertStringNotContainsString('PhotoResyncedException', $report);

		$ids_after = static::getRecentPhotoIDs();
		$ids_to_delete = $ids_after->diff($ids_before)->all();
		$this->photos_tests->delete($ids_to_delete);
	}

	public function testImportDuplicateWithoutResync(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// Upload the photo the first time and remove some information
		// such that we can be sure that **no** re-sync happens later
		$first_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$response = $this->photos_tests->get($first_id);
		$response->assertJson([
			'id' => $first_id,
			'make' => 'Canon',
			'model' => 'Canon EOS R',
		]);
		DB::table('photos')
			->where('id', '=', $first_id)
			->update(['make' => null, 'model' => null]);
		$response = $this->photos_tests->get($first_id);
		$response->assertJson([
			'id' => $first_id,
			'make' => null,
			'model' => null,
		]);

		// import the photo a second time and an do not skip the duplicate
		// but don't resync either
		// Hence, the original photo which has been duplicated
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false);
		$report = $this->photos_tests->import(base_path('public/uploads/import/'), null, false, false);
		static::assertStringNotContainsString('PhotoSkippedException', $report);
		static::assertStringNotContainsString('PhotoResyncedException', $report);

		// The original photo (which has been duplicated) should still
		// miss the meta-data which we removed intentionally
		$response = $this->photos_tests->get($first_id);
		$response->assertJson([
			'id' => $first_id,
			'make' => null,
			'model' => null,
		]);

		$ids_after = static::getRecentPhotoIDs();
		$ids_to_delete = $ids_after->diff($ids_before)->all();
		$this->photos_tests->delete($ids_to_delete);
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

	/**
	 * @return BaseCollection<string> the IDs of recently added photos
	 */
	protected static function getRecentPhotoIDs(): BaseCollection
	{
		$strRecent = Carbon::now()
			->subDays(intval(Configs::get_value('recent_age', '1')))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		return Photo::query()->select('id')->where($recentFilter)->pluck('id');
	}
}
