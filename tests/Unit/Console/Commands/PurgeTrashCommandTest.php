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

namespace Tests\Unit\Console\Commands;

use App\Actions\Photo\Trash;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

/**
 * Feature 119 (CLI-119-01, S-119-11): `lychee:purge-trash` empties the trash.
 */
class PurgeTrashCommandTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testTheCommandEmptiesTheTrash(): void
	{
		Bus::fake();
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		$photos = [
			Photo::factory()->owned_by($owner)->in($album)->create(),
			Photo::factory()->owned_by($owner)->in($album)->create(),
		];
		(new Trash())->fromAlbums([$album->id]);
		self::assertSame(2, DB::table('trashed_photos')->count());

		$this->artisan('lychee:purge-trash')
			->expectsOutput('Purged 2 photos, 0 restored, 0 left.')
			->assertExitCode(0);

		self::assertSame(0, DB::table('trashed_photos')->count());
		foreach ($photos as $photo) {
			$this->assertDatabaseMissing('photos', ['id' => $photo->id]);
		}
	}

	public function testTheCommandSucceedsOnAnEmptyTrash(): void
	{
		$this->artisan('lychee:purge-trash')
			->expectsOutput('Purged 0 photos, 0 restored, 0 left.')
			->assertExitCode(0);
	}
}
