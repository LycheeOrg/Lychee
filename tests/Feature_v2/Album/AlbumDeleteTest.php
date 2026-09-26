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

use App\Models\AccessPermission;
use App\Models\Statistics;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumDeleteTest extends BaseApiWithDataTest
{
	public function testDeleteAlbumUnauthorizedForbidden(): void
	{
		$response = $this->deleteJson('Album', []);
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertUnauthorized($response);

		// Deleting subAlbum1 follows the delete grant on its parent album1.
		AccessPermission::query()->where('id', '=', $this->perm1->id)->update(['grants_delete' => false]);
		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertDontSee($this->subAlbum1->id);
	}

	public function testDeleteAlbumAuthorizedUser(): void
	{
		// perm1 grants delete on album1, i.e. on its content:
		// its sub-album may be deleted, album1 itself may not.
		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Album', [
			'album_ids' => [$this->album1->id],
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Album', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertDontSee($this->subAlbum1->id);
		$this->assertEquals(0, Statistics::where('album_id', $this->subAlbum1->id)->count());
	}
}