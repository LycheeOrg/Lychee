<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Enum\StorageDiskType;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature_v2\Base\BaseApiTest;

/**
 * Simulates the pre-feature schema (a `track_short_path` column on `albums`,
 * no `tracks` table) then runs the migration file directly to verify the
 * backfill (S-055-13) and the reversible `down()` (NFR-055-04).
 */
class TrackMigrationTest extends BaseApiTest
{
	private const MIGRATION_PATH = 'database/migrations/2026_08_17_000001_create_tracks_table.php';

	private function runMigrationMethod(string $method): void
	{
		$migration = require base_path(self::MIGRATION_PATH);
		// @phpstan-ignore-next-line method.nonObject, method.notFound (anonymous migration class loaded dynamically)
		$migration->{$method}();
	}

	private function revertToPreFeatureSchema(): void
	{
		Schema::dropIfExists('tracks');
		if (!Schema::hasColumn('albums', 'track_short_path')) {
			Schema::table('albums', function ($table): void {
				$table->string('track_short_path')->nullable();
			});
		}
	}

	public function testBackfillCreatesOneTrackPerNonNullAlbumAndDropsColumn(): void
	{
		$owner = User::factory()->create();
		$albumWithTrack = Album::factory()->as_root()->create(['owner_id' => $owner->id]);
		$albumWithoutTrack = Album::factory()->as_root()->create(['owner_id' => $owner->id]);

		$this->revertToPreFeatureSchema();

		DB::table('albums')->where('id', '=', $albumWithTrack->id)->update([
			'track_short_path' => 'tracks/abc123.xml',
		]);

		$this->runMigrationMethod('up');

		self::assertFalse(Schema::hasColumn('albums', 'track_short_path'));

		$rows = DB::table('tracks')->where('album_id', '=', $albumWithTrack->id)->get();
		self::assertCount(1, $rows);
		$row = $rows->first();
		self::assertSame('abc123', $row->name);
		self::assertSame('tracks/abc123.xml', $row->file_name);
		self::assertSame(StorageDiskType::LOCAL->value, $row->disk);
		self::assertEquals(true, $row->is_primary);

		self::assertSame(0, DB::table('tracks')->where('album_id', '=', $albumWithoutTrack->id)->count());
	}

	public function testMigrationDownRestoresColumnFromPrimaryTrack(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->create(['owner_id' => $owner->id]);

		$this->revertToPreFeatureSchema();
		DB::table('albums')->where('id', '=', $album->id)->update([
			'track_short_path' => 'tracks/def456.xml',
		]);

		$this->runMigrationMethod('up');
		$this->runMigrationMethod('down');

		self::assertTrue(Schema::hasColumn('albums', 'track_short_path'));
		self::assertFalse(Schema::hasTable('tracks'));

		$restored = DB::table('albums')->where('id', '=', $album->id)->first();
		self::assertSame('tracks/def456.xml', $restored->track_short_path);

		// DatabaseTransactions rolls back all DDL made in this test (SQLite
		// supports transactional schema changes), restoring the real schema
		// for subsequent tests.
	}
}
