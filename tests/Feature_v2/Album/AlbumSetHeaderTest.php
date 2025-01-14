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

namespace Tests\Feature_v2\Album;

use App\Http\Controllers\Gallery\AlbumController;
use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumSetHeaderTest extends BaseApiV2Test
{
	public function testSetHeaderAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::header', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testSetHeaderAlbumAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::header', []);
		$this->assertUnprocessable($response);

		// Set the header ID
		$response = $this->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'header_id' => $this->photo1->id,
			],
		]);

		// Unset the header_id with second user (with grants_edit).
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'header_id' => null,
			],
		]);
	}

	public function testSetHeaderAlbumAllowedCompact(): void
	{
		// Set the header ID
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => 'aaaaaaaaaaaaaaaaaaaaaaaa',
			'is_compact' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'header_id' => AlbumController::COMPACT_HEADER,
			],
		]);

		// Unset the header_id with second user (with grants_edit).
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo1->id,
			'is_compact' => false,
		]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'header_id' => $this->photo1->id,
			],
		]);

		// Unset the header_id with second user (with grants_edit).
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::header', [
			'album_id' => $this->album1->id,
			'header_id' => $this->photo2->id,
			'is_compact' => false,
		]);
		$this->assertForbidden($response);
	}
}