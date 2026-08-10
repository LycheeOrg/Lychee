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
use App\Models\Album;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumMergeTest extends BaseApiWithDataTest
{
	public function testMergeAlbumDispatchesAlbumChildrenChangedButNoCoarseFlush(): void
	{
		Event::fake([AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		// album2 has subAlbum2 (a leaf, no grandchildren) as a child.
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::merge', [
			'album_id' => $this->album1->id, // has edit rights
			'album_ids' => [$this->album2->id], // own
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumChildrenChanged::class, fn (AlbumChildrenChanged $e) => in_array($this->album1->id, $e->parent_ids));
		Event::assertNotDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testMergeAlbumWithGrandchildDispatchesCoarseFlush(): void
	{
		// Build: sourceAlbum (owned by userMayUpload1) -> childAlbum -> grandchildAlbum
		$sourceAlbum = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$childAlbum = Album::factory()->children_of($sourceAlbum)->owned_by($this->userMayUpload1)->create();
		Album::factory()->children_of($childAlbum)->owned_by($this->userMayUpload1)->create();

		Event::fake([AlbumChildrenChanged::class, AlbumListingCacheFlushRequested::class]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::merge', [
			'album_id' => $this->album1->id,
			'album_ids' => [$sourceAlbum->id],
		]);
		$this->assertNoContent($response);

		Event::assertDispatched(AlbumChildrenChanged::class, fn (AlbumChildrenChanged $e) => in_array($this->album1->id, $e->parent_ids));
		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testMergeAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::merge', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::merge', [
			'album_id' => $this->album1->id,
			'album_ids' => [$this->album2->id],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Album::merge', [
			'album_id' => $this->album1->id,
			'album_ids' => [$this->album2->id],
		]);
		$this->assertForbidden($response);
	}

	public function testMergeAlbumAuthorizedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::merge', [
			'album_id' => $this->album1->id, // has edit rights
			'album_ids' => [$this->album2->id], // own
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->album1->id);
		$response->assertDontSee($this->album2->id);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
		$response->assertSee($this->subAlbum2->id);
	}
}