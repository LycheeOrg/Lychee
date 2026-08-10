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

use App\Events\AlbumChildrenChanged;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Models\AccessPermission;
use App\Models\Album;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumMoveTest extends BaseApiWithDataTest
{
	public function testMoveLeafAlbumDispatchesAlbumSavedAndChildrenChangedButNoCoarseFlush(): void
	{
		Event::fake([AlbumSaved::class, AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		// subAlbum1 is a leaf (no descendants), moved from album1 to root.
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($this->subAlbum1->id, $e->album_ids));
		Event::assertDispatched(AlbumChildrenChanged::class, fn (AlbumChildrenChanged $e) => in_array($this->album1->id, $e->parent_ids));
		Event::assertNotDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testMoveAlbumWithDescendantsDispatchesCoarseFlush(): void
	{
		$target = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		Event::fake([AlbumSaved::class, AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		// album1 has a descendant (subAlbum1), so moving it must trigger the coarse flush too.
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::move', [
			'album_id' => $target->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($this->album1->id, $e->album_ids));
		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

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