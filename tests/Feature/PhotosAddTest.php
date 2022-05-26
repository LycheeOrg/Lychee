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

		// Assert that photo table is empty, otherwise we cannot ensure
		// deterministic test results for duplicate photos
		static::assertDatabaseCount('sym_links', 0);
		static::assertDatabaseCount('size_variants', 0);
		static::assertDatabaseCount('photos', 0);

		AccessControl::log_as_id(0);
	}

	public function tearDown(): void
	{
		// Clean up remaining stuff from tests
		DB::table('sym_links')->delete();
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();
		self::cleanPublicFolders();

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
	 * A simple upload of an ordinary photo to the root album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToRoot(): void
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
	 * A simple upload of an ordinary photo to a regular album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToSubAlbum(): void
	{
		$album_id = null;

		try {
			$album_id = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');

			$id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE),
				$album_id
			);
			$this->photos_tests->get($id)->assertJson(['album_id' => $album_id]);
		} finally {
			if ($album_id) {
				$this->albums_tests->delete([$album_id]);
			}
		}
	}

	/**
	 * A simple upload of an ordinary photo to the public album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToPublic(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE),
			'public'
		);
		$this->photos_tests->get($id)->assertJson([
			'album_id' => null,
			'is_public' => 1,
		]);
	}

	/**
	 * A simple upload of an ordinary photo to the is-starred album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToIsStarred(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE),
			'starred'
		);
		$this->photos_tests->get($id)->assertJson([
			'album_id' => null,
			'is_starred' => true,
		]);
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

		$photo_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		);
		$video_id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_VIDEO),
			null,
			200
		);
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		static::assertEquals($photo_id, $video_id);
		static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
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
		// import the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, true, false, false);

		// check if the file has been moved
		static::assertEquals(false, file_exists(base_path('public/uploads/import/night.jpg')));
	}

	public function testImportViaCopy(): void
	{
		// import the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false, false);

		// check if the file is still there
		static::assertEquals(true, file_exists(base_path('public/uploads/import/night.jpg')));
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
	}

	public function testImportViaDeniedMove(): void
	{
		// import the photo without the right to move the photo (aka delete the original)
		// For POSIX system, the right to create/rename/delete/edit meta-attributes
		// of a file is based on the write-privilege of the containing directory,
		// because all these operations require an update of an directory entry.
		// Making the file read-only is not sufficient to prevent deletion.
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/read-only.jpg'));
		try {
			chmod(base_path('public/uploads/import/read-only.jpg'), 0444);
			chmod(base_path('public/uploads/import'), 0555);
			$this->photos_tests->import(base_path('public/uploads/import/'), null, true, false, false);

			// check if the file is still there
			static::assertEquals(true, file_exists(base_path('public/uploads/import/read-only.jpg')));
		} finally {
			// re-grant file access
			chmod(base_path('public/uploads/import'), 0775);
			chmod(base_path('public/uploads/import/read-only.jpg'), 0664);
		}
	}

	public function testUploadWithReadOnlyStorage(): void
	{
		self::restrictDirectoryAccess(base_path('public/uploads/'));

		$this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE),
			null,
			500,
			'Impossible to create the root directory'
		);
	}

	public function testImportSkipDuplicateWithResync(): void
	{
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
	}

	public function testImportSkipDuplicateWithoutResync(): void
	{
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
	}

	public function testImportDuplicateWithoutResync(): void
	{
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
	}

	/**
	 * Tests Apple Live Photo import via symlinks.
	 *
	 * @return void
	 */
	public function testAppleLivePhotoImportViaSymlink(): void
	{
		if (!$this->hasExifTools) {
			static::markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		$ids_before = static::getRecentPhotoIDs();

		// import the photo and video
		copy(base_path(TestCase::SAMPLE_FILE_TRAIN_IMAGE), base_path('public/uploads/import/train.jpg'));
		copy(base_path(TestCase::SAMPLE_FILE_TRAIN_VIDEO), base_path('public/uploads/import/train.mov'));
		$this->photos_tests->import(base_path('public/uploads/import/'), null, false, false, true);

		// check if the files are still there
		static::assertEquals(true, file_exists(base_path('public/uploads/import/train.jpg')));
		static::assertEquals(true, file_exists(base_path('public/uploads/import/train.mov')));

		// get the path of the photo object
		$ids_after = static::getRecentPhotoIDs();
		$photo_id = $ids_after->diff($ids_before)->first();
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		static::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		static::assertStringEndsWith('.mov', $photo->live_photo_url);
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		static::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

		// get the paths of the original size variant and the live photo and check whether they are truly symbolic links
		$symlink_path1 = public_path($photo->size_variants->original->url);
		$symlink_path2 = public_path($photo->live_photo_url);
		static::assertEquals(true, is_link($symlink_path1));
		static::assertEquals(true, is_link($symlink_path2));
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

	/**
	 * Recursively restricts the access to the given directory.
	 *
	 * @param string $dirPath the directory path
	 *
	 * @return void
	 */
	protected static function restrictDirectoryAccess(string $dirPath): void
	{
		if (!is_dir($dirPath)) {
			return;
		}

		$dirEntries = scandir($dirPath);
		foreach ($dirEntries as $dirEntry) {
			if (in_array($dirEntry, ['.', '..'])) {
				continue;
			}

			$dirEntryPath = $dirPath . DIRECTORY_SEPARATOR . $dirEntry;
			if (is_dir($dirEntryPath) && !is_link($dirEntryPath)) {
				self::restrictDirectoryAccess($dirEntryPath);
			}
		}

		\Safe\chmod($dirPath, 0555);
	}
}
