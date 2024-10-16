<?php

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

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumDeleteTest extends BaseApiV2Test
{
	public function testDeleteAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::delete', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::delete', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::delete', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::delete', [
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertDontSee($this->subAlbum1->id);
	}

	public function testDeleteAlbumAuthorizedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::delete', [
			'album_ids' => [$this->album1->id],
		]);
		$this->assertNoContent($response);

		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertDontSee($this->album1->id);
	}
}