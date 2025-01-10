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

namespace Tests\Traits;

use App\Enum\SmartAlbumType;
use Illuminate\Support\Facades\DB;

/**
 * Ensures that album-related tables are empty before and after a test.
 *
 * The name of the trait might be misleading.
 * The trait does not ensure that albums are empty, but that no albums
 * exist at all.
 * It ensures that the respective DB tables are empty.
 *
 * This trait does not take care of photos which might happen to be
 * in the albums.
 * Hence, this trait will raise exceptions if it tries to delete an album
 * which still contains photos due to violated foreign key constraints.
 * Hence, this trait should be used in combination with
 * {@link RequiresEmptyPhotos} and `RequiresEmptyPhotos` must run before
 * this trait.
 */
trait RequiresEmptyAlbums
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyAlbums(): void
	{
		// Assert that album tables are empty
		$this->assertDatabaseCount('base_albums', 0);
		$this->assertDatabaseCount('albums', 0);
		$this->assertDatabaseCount('tag_albums', 0);

		// We do not use assertDatabaseCount('access_permissions', 0)
		// Because we must not forget about the smart album properties too.
		static::assertEquals(
			0,
			DB::table('access_permissions')
				->whereNotIn('base_album_id', SmartAlbumType::values())
				->count()
		);
	}

	protected function tearDownRequiresEmptyAlbums(): void
	{
		// Clean up remaining stuff from tests
		// For MySQL/MariaDB we must delete albums in the correct order to
		// avoid breaking parent relationship although this is non-standard
		// SQL.
		DB::table('tag_albums')->delete();
		DB::table('albums')->orderBy('_lft', 'desc')->delete();
		DB::table('base_albums')->delete();
		DB::table('access_permissions')->whereNotIn('base_album_id', SmartAlbumType::values())->delete();
	}
}
