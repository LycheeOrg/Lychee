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

namespace Tests\Feature_v2;

use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-04, S-060-11): the backfill migrations compute
 * `title_base`/`title_index` for pre-existing rows (i.e. rows created
 * through a path that never called `TitleSplitter::split()`, exactly like
 * a factory-seeded row or a pre-upgrade DB row) and are idempotent.
 */
class TitleBackfillTest extends BaseApiWithDataTest
{
	public function testBackfillPopulatesPhotosDerivedColumns(): void
	{
		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('backfill_test_5')
			->in($this->album1)
			->create();

		// Factories bypass every explicit write site (FR-060-03), so the
		// derived columns start out un-backfilled.
		$this->assertNull($photo->fresh()->title_base);
		$this->assertNull($photo->fresh()->title_index);

		$this->runPhotoBackfillMigration();

		$photo->refresh();
		$this->assertSame('backfill_test_', $photo->title_base);
		$this->assertSame(5, $photo->title_index);

		// S-060-11: re-running is a no-op.
		$this->runPhotoBackfillMigration();
		$photo->refresh();
		$this->assertSame('backfill_test_', $photo->title_base);
		$this->assertSame(5, $photo->title_index);
	}

	public function testBackfillPopulatesBaseAlbumsDerivedColumns(): void
	{
		$album = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('backfill_album_7')
			->create();

		$this->assertSame('', BaseAlbumImpl::query()->findOrFail($album->id)->title_base);
		$this->assertNull(BaseAlbumImpl::query()->findOrFail($album->id)->title_index);

		$this->runBaseAlbumBackfillMigration();

		$base_album = BaseAlbumImpl::query()->findOrFail($album->id);
		$this->assertSame('backfill_album_', $base_album->title_base);
		$this->assertSame(7, $base_album->title_index);

		// S-060-11: re-running is a no-op.
		$this->runBaseAlbumBackfillMigration();
		$base_album = BaseAlbumImpl::query()->findOrFail($album->id);
		$this->assertSame('backfill_album_', $base_album->title_base);
		$this->assertSame(7, $base_album->title_index);
	}

	private function runPhotoBackfillMigration(): void
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_08_28_000003_backfill_title_sorting_columns_for_photos.php');
		$migration->up();
	}

	private function runBaseAlbumBackfillMigration(): void
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_08_28_000004_backfill_title_sorting_columns_for_base_albums.php');
		$migration->up();
	}
}
