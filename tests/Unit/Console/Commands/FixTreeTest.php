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

use App\Events\AlbumListingCacheFlushRequested;
use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class FixTreeTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testNoopRunDoesNotDispatchCoarseFlush(): void
	{
		$user = User::factory()->create();
		Album::factory()->as_root()->owned_by($user)->create();

		Event::fake([AlbumListingCacheFlushRequested::class]);

		$this->artisan('lychee:fix-tree')->assertSuccessful();

		Event::assertNotDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testRepairingACorruptedTreeDispatchesCoarseFlush(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Force `_lft >= _rgt`, an "oddness" error `fixTree()` will repair.
		DB::table('albums')->where('id', $album->id)->update(['_rgt' => $album->_lft]);

		Event::fake([AlbumListingCacheFlushRequested::class]);

		$this->artisan('lychee:fix-tree')->assertSuccessful();

		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}
}
