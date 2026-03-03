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

namespace Tests\ImageProcessing\Commands;

use App\Constants\PhotoAlbum as PA;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SyncTest extends BaseApiWithDataTest
{
	public const COMMAND = 'lychee:sync';

	// S-024-01 regression guard: conflicting flags still fail
	public function testFailedArgs(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--delete_imported' => '1',
			'-v' => '1',
			'paths' => ['.'],
		])
		->assertFailed()
		->assertExitCode(1);
	}

	// S-024-01: directory sync behaviour unchanged
	public function testSuccess(): void
	{
		Configs::set('skip_duplicates_early', '1');
		Configs::set('sync_delete_missing_photos', '1');
		Configs::set('sync_delete_missing_albums', '1');

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => ['./tests/Samples/sync'],
		])
		->assertSuccessful();

		$album = DB::table('base_albums')->select('id')->where('title', 'sync')->first();
		$this->assertNotNull($album, 'Album "sync" should have been created.');

		$photo = DB::table('photos')->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')->where(PA::ALBUM_ID, $album->id)->first();
		$this->assertNotNull($photo, 'Photo "png" should have been created.');
		$this->assertEquals('png', $photo->title, 'Photo title should be "png".');

		// Second run should not create duplicates
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'paths' => ['./tests/Samples/sync'],
		])
		->assertSuccessful();

		$this->assertEquals(1, DB::table('base_albums')->select('id')->where('title', 'sync')->count());
		$this->assertEquals(1, DB::table('photos')->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')->where(PA::ALBUM_ID, $album->id)->count());

		Configs::set('skip_duplicates_early', '0');
		Configs::set('sync_delete_missing_photos', '0');
		Configs::set('sync_delete_missing_albums', '0');
	}

	// S-024-02: single file path with supported extension is imported into the target album
	public function testSyncSingleFile(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/png.png'],
		])
		->assertSuccessful();

		$photo = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->first();
		$this->assertNotNull($photo, 'Photo should have been imported into the target album.');
		$this->assertEquals('png', $photo->title, 'Photo title should be "png".');
	}

	// S-024-03: unsupported extension is warned and skipped, exit 0
	public function testSyncFileUnsupportedExtension(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/xcf.xcf'],
		])
		->assertSuccessful();

		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(0, $count, 'No photos should have been imported for unsupported extension.');
	}

	// S-024-04: non-existent path produces error and exits 1
	public function testSyncNonExistentPath(): void
	{
		$this->artisan(self::COMMAND, [
			'--owner_id' => $this->admin->id,
			'paths' => ['/nonexistent/path/to/file.jpg'],
		])
		->assertFailed()
		->assertExitCode(1);
	}

	// S-024-05 / S-024-08: multiple file paths are all imported
	public function testSyncMultipleFiles(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => [
				'./tests/Samples/png.png',
				'./tests/Samples/gif.gif',
			],
		])
		->assertSuccessful();

		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(2, $count, 'Both files should have been imported.');
	}

	// S-024-05: mixed directory and file paths are processed together
	public function testSyncMixedPaths(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => [
				'./tests/Samples/sync',
				'./tests/Samples/png.png',
			],
		])
		->assertSuccessful();

		// Directory sync should have created a "sync" sub-album under album5
		$sub_album = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('albums.parent_id', $this->album5->id)
			->where('base_albums.title', 'sync')
			->first();
		$this->assertNotNull($sub_album, 'Sub-album "sync" should have been created from directory sync.');

		// The file should have been imported directly into album5
		$direct_photo = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->first();
		$this->assertNotNull($direct_photo, 'File should have been imported directly into album5.');
	}

	// S-024-07: delete_missing flags are inactive when only file paths are supplied
	public function testSyncFileDeleteMissingNotice(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--delete_missing_photos' => '1',
			'--owner_id' => $this->admin->id,
			'--album_id' => $this->album5->id,
			'paths' => ['./tests/Samples/gif.gif'],
		])
		->assertSuccessful();

		// Verify the file was imported (command ran successfully despite delete_missing_photos=1)
		$count = DB::table('photos')
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $this->album5->id)
			->count();
		$this->assertEquals(1, $count, 'GIF should have been imported even when delete_missing_photos=1 is set for file mode.');
	}
}