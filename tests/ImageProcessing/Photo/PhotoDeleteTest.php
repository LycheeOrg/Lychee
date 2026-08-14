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

namespace Tests\ImageProcessing\Photo;

use App\Events\AlbumSaved;
use App\Events\TagAlbumSaved;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoDeleteTest extends BaseApiWithDataTest
{
	public function testForceDeletingPhotoNullifiesCrossAlbumCoverAndDispatchesAlbumSaved(): void
	{
		// photo3 is the only photo in album3 (owned by userNoUpload), so deleting
		// it from album3 will force-delete it. Set it as album2's cover — a
		// *different* album than the one the deletion request originates from.
		$response = $this->actingAs($this->admin)->postJson('Album::cover', [
			'album_id' => $this->album2->id,
			'photo_id' => $this->photo3->id,
		]);
		$this->assertNoContent($response);
		$this->album2->refresh();
		$this->assertSame($this->photo3->id, $this->album2->cover_id);

		Event::fake([AlbumSaved::class]);

		$response = $this->actingAs($this->admin)->deleteJson('Photo', [
			'photo_ids' => [$this->photo3->id],
			'from_id' => $this->album3->id,
		]);
		$this->assertNoContent($response);

		$this->album2->refresh();
		$this->assertNull($this->album2->cover_id);
		Event::assertDispatched(AlbumSaved::class, fn (AlbumSaved $e) => in_array($this->album2->id, $e->album_ids));
	}

	public function testForceDeletingPhotoNullifiesCrossAlbumTagAlbumCoverAndDispatchesTagAlbumSaved(): void
	{
		$tag_album = TagAlbum::factory()->owned_by($this->admin)->create();
		$tag_album->cover_id = $this->photo3->id;
		$tag_album->save();

		Event::fake([TagAlbumSaved::class]);

		$response = $this->actingAs($this->admin)->deleteJson('Photo', [
			'photo_ids' => [$this->photo3->id],
			'from_id' => $this->album3->id,
		]);
		$this->assertNoContent($response);

		$tag_album->refresh();
		$this->assertNull($tag_album->cover_id);
		Event::assertDispatched(TagAlbumSaved::class, fn (TagAlbumSaved $e) => in_array($tag_album->id, $e->tag_album_ids, true));
	}

	public function testDeletePhotoUnauthorizedForbidden(): void
	{
		$response = $this->deleteJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => $this->album2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeletePhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'photos');

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'photos');
	}
}