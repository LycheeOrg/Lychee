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

namespace Tests\Feature_v2\Album;

use App\Events\AlbumSaved;
use App\Models\AccessPermission;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumMoveTest extends BaseApiWithDataTest
{
	public function testMoveAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::move', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testMoveAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
	}

	public function testMoveAlbumToRootDispatchesAlbumSaved(): void
	{
		Event::fake([AlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $event) => $event->album->id === $this->subAlbum1->id);
	}

	public function testMoveAlbumIntoAnotherAlbumDispatchesAlbumSavedForMovedAlbumAndOldParent(): void
	{
		Event::fake([AlbumSaved::class]);

		// subAlbum1 starts out as a child of album1; moving it to album5 changes its parent.
		$response = $this->actingAs($this->admin)->postJson('Album::move', [
			'album_id' => $this->album5->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		// The moved album itself, plus its *old* parent (album1) — S-052-06: moving an
		// album must invalidate both the old and the new parent's cached children list.
		// (album5, the new parent, is covered separately via the moved album's own
		// post-move parent_id — see ManagedCacheAlbumInvalidator::handleAlbumSaved.)
		Event::assertDispatched(AlbumSaved::class, 2);
		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $event) => $event->album->id === $this->subAlbum1->id);
		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $event) => $event->album->id === $this->album1->id);
	}

	public function testMoveAlbumAuthorizedUser(): void
	{
		AccessPermission::factory()
		->for_user($this->userMayUpload2)
		->for_album($this->subAlbum1)
		->visible()
		->grants_edit()
		->grants_delete()
		->grants_upload()
		->grants_download()
		->grants_full_photo()
		->create();

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
	}
}