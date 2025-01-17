<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\Photo;

use Tests\Feature_v2\Base\BaseApiV2Test;

class PhotoEditTest extends BaseApiV2Test
{
	public function testEditPhotoUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'upload_date' => '2021-01-01',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'upload_date' => '2021-01-01',
		]);
		$this->assertForbidden($response);
	}

	public function testEditPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'title' => 'new title',
						'description' => 'new description',
						'tags' => ['tag1'],
						'license' => 'none',
						'created_at' => '2021-01-01T00:00:00+00:00',
					],
				],
			],
		]);
	}

	public function testSetStarPhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Photo::star', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testSetStarPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::star', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'is_starred' => true,
					],
				],
			],
		]);
	}

	public function testMovePhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Photo::move', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::move', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::move', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testMovePhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::move', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::move', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::move', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->subAlbum1->id,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->subAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
					],
				],
			],
		]);
	}

	public function testCopyPhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Photo::copy', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::copy', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::copy', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testCopyPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::copy', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::copy', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->subAlbum1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::copy', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->subAlbum1->id,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->subAlbum1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');
	}
}