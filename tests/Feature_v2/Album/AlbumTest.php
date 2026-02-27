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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumTest extends BaseApiWithDataTest
{
	public function testGet(): void
	{
		$response = $this->getJson('Album::head');
		$this->assertUnprocessable($response);
		$response->assertJson([
			'message' => 'The album id field is required.',
		]);
	}

	public function testGetAnon(): void
	{
		// Test album head (metadata)
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_password_protected' => false,
				'is_search_accessible' => false,
			],
			'resource' => [
				'id' => $this->album4->id,
				'title' => $this->album4->title,
			],
		]);

		// Test child albums
		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'data' => [
				[
					'id' => $this->subAlbum4->id,
					'title' => $this->subAlbum4->title,
					'is_public' => true,
					'thumb' => [
						'id' => $this->subPhoto4->id,
					],
				],
			],
		]);

		// Test photos
		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photos' => [
				[
					'id' => $this->photo4->id,
				],
			],
		]);
	}

	public function testGetAsGroup(): void
	{
		// Test album head (metadata)
		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
			],
		]);

		// Test child albums (should be empty for group user - they don't see subAlbum1)
		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(0, 'data');

		// Test photos
		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'photos');
		$response->assertJson([
			'photos' => [
				[
					'id' => $this->photo1->id,
				],
				[
					'id' => $this->photo1b->id,
				],
			],
		]);
	}

	public function testGetAsOwner(): void
	{
		// Test tag album head (metadata)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => false,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->tagAlbum1->id,
				'title' => $this->tagAlbum1->title,
				'show_tags' => [$this->tag_test->name],
			],
		]);

		// Test tag album photos
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photos' => [
				[
					'id' => $this->photo1->id,
				],
			],
		]);
	}

	public function testGetUnauthorizedOrForbidden(): void
	{
		// Unauthorized if not logged in.
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);

		// Forbidden if logged in.
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}
}