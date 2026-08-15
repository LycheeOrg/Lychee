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

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\SetSmartProtectionPolicy;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Factories\AlbumFactory;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class SetSmartProtectionPolicyTest extends AbstractTestCase
{
	/**
	 * Regression test for the removed FR-053-17: `Top()`'s smart-album
	 * section is never cached (always computed live via `Gate::check()`),
	 * so there is nothing for a policy change to invalidate — no album-listing
	 * cache event should be dispatched at all.
	 */
	public function testDoDispatchesNoAlbumListingCacheEvent(): void
	{
		$events = [
			AlbumSaved::class,
			TagAlbumSaved::class,
			PersonAlbumSaved::class,
			AlbumChildrenChanged::class,
			AlbumListingCacheFlushRequested::class,
		];
		Event::fake($events);

		$smart_album = resolve(AlbumFactory::class)->getAllBuiltInSmartAlbums(false)->first();
		self::assertNotNull($smart_album);

		(new SetSmartProtectionPolicy())->do($smart_album, true);
		(new SetSmartProtectionPolicy())->do($smart_album, false);

		foreach ($events as $event) {
			Event::assertNotDispatched($event);
		}
	}
}
