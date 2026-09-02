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

namespace Tests\Feature_v2\Settings;

use App\Models\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 062 (FR-062-08, S-062-13, FX-062-02): upgrading an existing
 * install with `owner_id`-valued `sorting_albums_col` (instance config) or
 * `albums.album_sorting_col` (per-album override) rewrites both to
 * `created_at`.
 */
class OwnerIdSortingMigrationTest extends BaseApiWithDataTest
{
	public function testSeededPreUpgradeConfigValueIsRewrittenToCreatedAt(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'owner_id']);

		$this->runMigration();

		$this->assertSame('created_at', DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('value'));
	}

	public function testSeededPreUpgradeAlbumColumnValueIsRewrittenToCreatedAt(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('albums')->where('id', '=', $album->id)->update(['album_sorting_col' => 'owner_id']);

		$this->runMigration();

		$this->assertSame('created_at', DB::table('albums')->where('id', '=', $album->id)->value('album_sorting_col'));
	}

	public function testOtherValuesAreUntouched(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'title']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('albums')->where('id', '=', $album->id)->update(['album_sorting_col' => 'title']);

		$this->runMigration();

		$this->assertSame('title', DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('value'));
		$this->assertSame('title', DB::table('albums')->where('id', '=', $album->id)->value('album_sorting_col'));
	}

	/**
	 * NFR-062-07: re-running the migration is a no-op.
	 */
	public function testReRunningMigrationIsANoOp(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'owner_id']);
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('albums')->where('id', '=', $album->id)->update(['album_sorting_col' => 'owner_id']);

		$this->runMigration();
		$first_pass = [
			'config' => DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('value'),
			'album' => DB::table('albums')->where('id', '=', $album->id)->value('album_sorting_col'),
		];

		$this->runMigration();
		$second_pass = [
			'config' => DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('value'),
			'album' => DB::table('albums')->where('id', '=', $album->id)->value('album_sorting_col'),
		];

		$this->assertEquals($first_pass, $second_pass);
	}

	public function testMigrationHasNoDownMethodBehaviour(): void
	{
		$migration = $this->loadMigration();
		$migration->down();
		// No exception, no-op — "no coming back" (NFR-062-07).
		$this->addToAssertionCount(1);
	}

	private function runMigration(): void
	{
		$this->loadMigration()->up();
	}

	private function loadMigration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_09_02_120000_remove_owner_id_album_sorting.php');

		return $migration;
	}
}
