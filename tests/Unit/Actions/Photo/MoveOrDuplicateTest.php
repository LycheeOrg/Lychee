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

namespace Tests\Unit\Actions\Photo;

use App\Actions\Photo\MoveOrDuplicate;
use App\Actions\Shop\PurchasableService;
use App\Contracts\Models\AbstractAlbum;
use App\Events\AlbumSaved;
use App\Events\PhotoDeleted;
use App\Events\PhotoMoved;
use App\Events\PhotoSaved;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class MoveOrDuplicateTest extends AbstractTestCase
{
	use DatabaseTransactions;

	/**
	 * Regression test: moving photos to the root album (i.e. $to_album ===
	 * null) must still dispatch PhotoSaved for the moved photos so that
	 * listeners depending on a photo's containing albums (e.g. the
	 * PersonAlbum "matching albums" cache) are invalidated. Before the fix,
	 * PhotoSaved was only dispatched when a link was gained ($to_album !==
	 * null), so a move to root silently skipped it.
	 */
	public function testMoveToRootAlbumDispatchesPhotoSaved(): void
	{
		// Fake only the app-level events dispatched by MoveOrDuplicate::do():
		// a blanket Event::fake() would also intercept Eloquent's internal
		// model lifecycle events (creating/saved/...), which the nested-set
		// album factories in this test rely on.
		Event::fake([AlbumSaved::class, PhotoDeleted::class, PhotoSaved::class, PhotoMoved::class]);

		$user = User::factory()->create();
		/** @var Album&AbstractAlbum $album */
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		$move_action->do(collect([$photo]), $album, null);

		Event::assertDispatched(PhotoSaved::class, fn (PhotoSaved $event) => $event->photo_ids === [$photo->id]);
		// A move to root is not a cross-album move: PhotoMoved must not fire.
		Event::assertNotDispatched(PhotoMoved::class);
	}

	public function testCrossAlbumMoveDispatchesPhotoSavedAndPhotoMoved(): void
	{
		// Fake only the app-level events dispatched by MoveOrDuplicate::do():
		// a blanket Event::fake() would also intercept Eloquent's internal
		// model lifecycle events (creating/saved/...), which the nested-set
		// album factories in this test rely on.
		Event::fake([AlbumSaved::class, PhotoDeleted::class, PhotoSaved::class, PhotoMoved::class]);

		$user = User::factory()->create();
		/** @var Album&AbstractAlbum $source_album */
		$source_album = Album::factory()->as_root()->owned_by($user)->create();
		$dest_album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($source_album->id);

		$move_action = new MoveOrDuplicate(resolve(PurchasableService::class));
		$move_action->do(collect([$photo]), $source_album, $dest_album);

		Event::assertDispatched(PhotoSaved::class, fn (PhotoSaved $event) => $event->photo_ids === [$photo->id]);
		Event::assertDispatched(PhotoMoved::class, fn (PhotoMoved $event) => $event->photo_ids === [$photo->id] &&
			$event->from_album_id === $source_album->id &&
			$event->to_album_id === $dest_album->id);
	}
}
