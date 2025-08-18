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

namespace Tests\Feature_v2\Commands;

use App\Constants\PhotoAlbum as PA;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SyncTest extends BaseApiWithDataTest
{
	public const COMMAND = 'lychee:sync';

	public function testFailedArgs(): void
	{
		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--delete_imported' => '1',
			'-v' => '1',
			'dir' => ['.'],
		])
		->assertFailed()
		->assertExitCode(1);
	}

	public function testSuccess(): void
	{
		Configs::set('skip_duplicates_early', '1');
		Configs::set('sync_delete_missing_photos', '1');
		Configs::set('sync_delete_missing_albums', '1');
		Configs::invalidateCache();

		$this->artisan(self::COMMAND, [
			'--import_via_symlink' => '1',
			'--skip_duplicates' => '0',
			'--delete_imported' => '0',
			'--owner_id' => $this->admin->id,
			'dir' => ['./tests/Samples/sync'],
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
			'dir' => ['./tests/Samples/sync'],
		])
		->assertSuccessful();

		$this->assertEquals(1, DB::table('base_albums')->select('id')->where('title', 'sync')->count());
		$this->assertEquals(1, DB::table('photos')->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')->where(PA::ALBUM_ID, $album->id)->count());

		Configs::set('skip_duplicates_early', '0');
		Configs::set('sync_delete_missing_photos', '0');
		Configs::set('sync_delete_missing_albums', '0');
		Configs::invalidateCache();
	}
}