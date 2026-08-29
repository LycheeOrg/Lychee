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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-10, S-060-07, S-060-08): upgrading an existing install
 * with `title_strict`/`description`/`description_strict`-valued sort configs
 * auto-migrates them to `title`, and narrows `type_range` accordingly.
 */
class SortingConfigMigrationTest extends BaseApiWithDataTest
{
	public function testSeededPreUpgradeValuesAreRewrittenToTitle(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update([
			'value' => 'title_strict',
			'type_range' => 'created_at|taken_at|title|description|is_highlighted|type|title_strict|description_strict',
		]);
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update([
			'value' => 'description',
			'type_range' => 'created_at|title|description|max_taken_at|min_taken_at|title_strict|description_strict',
		]);
		DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->update([
			'value' => 'description_strict',
			'type_range' => 'created_at|title|description|max_taken_at|min_taken_at|title_strict|description_strict',
		]);

		$this->runMigration();

		$this->assertSame('title', DB::table('configs')->where('key', '=', 'sorting_photos_col')->value('value'));
		$this->assertSame('created_at|taken_at|title|is_highlighted|type', DB::table('configs')->where('key', '=', 'sorting_photos_col')->value('type_range'));

		$this->assertSame('title', DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('value'));
		$this->assertSame('created_at|title|max_taken_at|min_taken_at', DB::table('configs')->where('key', '=', 'sorting_albums_col')->value('type_range'));

		$this->assertSame('title', DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->value('value'));
		$this->assertSame('created_at|title|max_taken_at|min_taken_at', DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->value('type_range'));
	}

	public function testOtherValuesAreUntouched(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'created_at']);

		$this->runMigration();

		$this->assertSame('created_at', DB::table('configs')->where('key', '=', 'sorting_photos_col')->value('value'));
	}

	/**
	 * S-060-07/08: re-running the migration is a no-op.
	 */
	public function testReRunningMigrationIsANoOp(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_photos_col')->update(['value' => 'title_strict']);
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'description']);
		DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->update(['value' => 'description_strict']);

		$this->runMigration();
		$firstPass = [
			'photos' => DB::table('configs')->where('key', '=', 'sorting_photos_col')->first(),
			'albums' => DB::table('configs')->where('key', '=', 'sorting_albums_col')->first(),
			'pinned' => DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->first(),
		];

		$this->runMigration();
		$secondPass = [
			'photos' => DB::table('configs')->where('key', '=', 'sorting_photos_col')->first(),
			'albums' => DB::table('configs')->where('key', '=', 'sorting_albums_col')->first(),
			'pinned' => DB::table('configs')->where('key', '=', 'sorting_pinned_albums_col')->first(),
		];

		$this->assertEquals($firstPass, $secondPass);
	}

	private function runMigration(): void
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_08_28_000005_migrate_sorting_config_to_unified_title.php');
		$migration->up();
	}
}
