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

namespace Tests\Feature_v1;

use App\Image\Files\BaseMediaFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Models\Photo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use function Safe\copy;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;

/**
 * Contains all tests for the various ways of adding images to Lychee
 * (upload, download, import) and their various options.
 */
class PhotosAddMethodsTest extends BasePhotoTest
{
	public function testImportViaMove(): void
	{
		// import the photo
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		self::assertEquals(true, file_exists(static::importPath('night.jpg')));
		$this->photos_tests->importFromServer(
			path: static::importPath(),
			album_id: null,
			delete_imported: true,
			skip_duplicates: false,
			import_via_symlink: false);
		// check if the file has been moved
		self::assertEquals(false, file_exists(static::importPath('night.jpg')));
	}

	public function testImportViaCopy(): void
	{
		// import the photo
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		$this->photos_tests->importFromServer(
			path: static::importPath(),
			album_id: null,
			delete_imported: false,
			skip_duplicates: false,
			import_via_symlink: false);

		// check if the file is still there
		self::assertEquals(true, file_exists(static::importPath('night.jpg')));
	}

	public function testImportViaSymlink(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		// import the photo
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		$this->photos_tests->importFromServer(static::importPath(), null, false, false, true);

		// check if the file is still there
		self::assertEquals(true, file_exists(static::importPath('night.jpg')));

		// get the path of the photo object and check whether it is truly a symbolic link
		$ids_after = static::getRecentPhotoIDs();
		$photo_id = $ids_after->diff($ids_before)->first();
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		/** @disregard */
		$symlink_path = public_path($this->dropUrlPrefix($photo->size_variants->original->url));
		self::assertEquals(true, is_link($symlink_path));
	}

	public function testImportSkipDuplicateWithResync(): void
	{
		// Upload the photo the first time and remove some information
		// such that there is really something to re-sync
		$first_id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
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
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		$report = $this->photos_tests->importFromServer(static::importPath(), null, false, true, false, true);
		self::assertStringNotContainsString('PhotoSkippedException', $report);
		self::assertStringContainsString('PhotoResyncedException', $report);

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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);

		// import the photo a second time and skip the duplicate
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		$report = $this->photos_tests->importFromServer(static::importPath(), null, false, true, false, false);
		self::assertStringContainsString('PhotoSkippedException', $report);
		self::assertStringNotContainsString('PhotoResyncedException', $report);
	}

	public function testImportDuplicateWithoutResync(): void
	{
		// Upload the photo the first time and remove some information
		// such that we can be sure that **no** re-sync happens later
		$first_id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
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

		// Import the photo a second time and do not skip the duplicate,
		// but don't resync the metadata either.
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('night.jpg'));
		$this->photos_tests->importFromServer(static::importPath(), null, false, false);
		$report = $this->photos_tests->importFromServer(static::importPath(), null, false, false);
		self::assertStringNotContainsString('PhotoSkippedException', $report);
		self::assertStringNotContainsString('PhotoResyncedException', $report);

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
		$this->assertHasExifToolOrSkip();

		$ids_before = static::getRecentPhotoIDs();

		// import the photo and video
		copy(base_path(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), static::importPath('train.jpg'));
		copy(base_path(TestConstants::SAMPLE_FILE_TRAIN_VIDEO), static::importPath('train.mov'));
		$this->photos_tests->importFromServer(static::importPath(), null, false, false, true);

		// check if the files are still there
		self::assertEquals(true, file_exists(static::importPath('train.jpg')));
		self::assertEquals(true, file_exists(static::importPath('train.mov')));

		// get the path of the photo object
		$ids_after = static::getRecentPhotoIDs();
		$photo_id = $ids_after->diff($ids_before)->first();
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		self::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		self::assertStringEndsWith('.mov', $photo->live_photo_url);
		/** @disregard */
		self::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		/** @disregard */
		self::assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));

		// get the paths of the original size variant and the live photo and check whether they are truly symbolic links
		/** @disregard */
		$symlink_path1 = public_path($this->dropUrlPrefix($photo->size_variants->original->url));
		$symlink_path2 = public_path($this->dropUrlPrefix($photo->live_photo_url));
		self::assertEquals(true, is_link($symlink_path1));
		self::assertEquals(true, is_link($symlink_path2));
	}

	public function testImportFromUrl(): void
	{
		$response = $this->photos_tests->importFromUrl([TestConstants::SAMPLE_DOWNLOAD_JPG]);

		$response->assertJson([[
			'album_id' => null,
			'title' => 'mongolia',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'original' => [
					'width' => 1280,
					'height' => 850,
					'filesize' => 201316,
				],
			],
		]]);
	}

	public function testImportFromUrlWithoutExtension(): void
	{
		$response = $this->photos_tests->importFromUrl([TestConstants::SAMPLE_DOWNLOAD_JPG_WITHOUT_EXTENSION]);

		$response->assertJson([[
			'album_id' => null,
			'title' => 'mongolia',
			'type' => TestConstants::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'original' => [
					'width' => 1280,
					'height' => 850,
					'filesize' => 201316,
				],
			],
		]]);
	}

	/**
	 * Test import from URL of a supported raw image.
	 *
	 * This test is necessary in addition to uploading a raw file, because
	 * Lychee checks whether the format is supported prior to the download,
	 * i.e. before an actual file exists on the server and hence this check
	 * takes a different code path than the check after an upload.
	 *
	 * @return void
	 */
	public function testRawImportFromUrl(): void
	{
		$acceptedRawFormats = Configs::getValueAsString(TestConstants::CONFIG_RAW_FORMATS);
		try {
			Configs::set(TestConstants::CONFIG_RAW_FORMATS, '.tif');
			$reflection = new \ReflectionClass(BaseMediaFile::class);
			$reflection->setStaticPropertyValue('cachedAcceptedRawFileExtensions', []);

			$response = $this->photos_tests->importFromUrl([TestConstants::SAMPLE_DOWNLOAD_TIFF]);

			$response->assertJson([[
				'album_id' => null,
				'title' => 'tiff',
				'type' => TestConstants::MIME_TYPE_IMG_TIFF,
				'size_variants' => [
					'original' => [
						'width' => 400,
						'height' => 300,
						'filesize' => 5802,
					],
				],
			]]);
		} finally {
			Configs::set(TestConstants::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		}
	}

	public function testJobUploadWithFaking(): void
	{
		$useJobQueues = Configs::getValueAsString(TestConstants::CONFIG_USE_JOB_QUEUES);
		try {
			Configs::set(TestConstants::CONFIG_USE_JOB_QUEUES, '1');

			Queue::fake();
			Queue::assertNothingPushed();

			$this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
			);

			Queue::assertPushed(ProcessImageJob::class, 1);

			self::assertEquals(1, JobHistory::where('status', '=', '0')->count());
			self::assertEquals(1, Queue::size());
		} finally {
			Configs::set(TestConstants::CONFIG_USE_JOB_QUEUES, $useJobQueues);
		}
	}

	public function testJobUploadWithoutFaking(): void
	{
		$useJobQueues = Configs::getValueAsString(TestConstants::CONFIG_USE_JOB_QUEUES);
		$defaultQueue = Config::get('queue.default', 'sync');
		try {
			Configs::set(TestConstants::CONFIG_USE_JOB_QUEUES, '1');
			Config::set('queue.default', 'sync');

			$this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
			);

			self::assertEquals(1, JobHistory::where('status', '=', '1')->count());
			$response = $this->get('Jobs');
			$this->assertOk($response);

			self::assertEquals(1, Photo::query()->count());
		} finally {
			Configs::set(TestConstants::CONFIG_USE_JOB_QUEUES, $useJobQueues);
			Config::set('queue.default', $defaultQueue);
		}
	}
}
