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
use App\Events\TagAlbumSaved;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumTransferTest extends BaseApiWithDataTest
{
	public function testTransferRootAlbumWithDescendantsDispatchesAlbumSavedAndCoarseFlush(): void
	{
		Event::fake([AlbumSaved::class, AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		// album1 is root (no old parent) and has subAlbum1 as a descendant.
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userLocked->id,
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($this->album1->id, $e->album_ids));
		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
		Event::assertNotDispatched(AlbumChildrenChanged::class);
	}

	public function testTransferLeafSubAlbumDispatchesAlbumChildrenChangedForOldParent(): void
	{
		Event::fake([AlbumSaved::class, AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		// subAlbum1 is a leaf child of album1.
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->subAlbum1->id,
			'user_id' => $this->userLocked->id,
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($this->subAlbum1->id, $e->album_ids));
		Event::assertDispatched(AlbumChildrenChanged::class, fn (AlbumChildrenChanged $e) => in_array($this->album1->id, $e->parent_ids));
		Event::assertNotDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testTransferTagAlbumDispatchesTagAlbumSavedNotAlbumSaved(): void
	{
		Event::fake([AlbumSaved::class, TagAlbumSaved::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->tagAlbum1->id,
			'user_id' => $this->userLocked->id,
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => $e->tag_album_ids === [$this->tagAlbum1->id]);
		Event::assertNotDispatched(AlbumSaved::class);
	}

	public function testTransferAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::transfer', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userMayUpload2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userMayUpload2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testTransferAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userLocked->id,
		]);
		$this->assertNoContent($response);
		$response = $this->actingAs($this->userLocked)->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->album1->id);
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
	}
}