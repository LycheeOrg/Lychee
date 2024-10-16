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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Album::get');
		$response->assertUnprocessable();
		$response->assertJson([
			'message' => 'The album id field is required.',
		]);
	}

	public function testGetAnon(): void
	{
		$response = $this->getJsonWithData('Album::get', ['album_id' => $this->album4->id]);
		$response->assertOk();
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => true,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => false,
			],
			'resource' => [
				'id' => $this->album4->id,
				'title' => $this->album4->title,
				'albums' => [
					[
						'id' => $this->subAlbum4->id,
						'title' => $this->subAlbum4->title,
						'is_public' => true,
						'thumb' => [
							'id' => $this->subPhoto4->id,
						],
					],
				],
				'photos' => [
					[
						'id' => $this->photo4->id,
					],
				],
			],
		]);
	}

	public function testGetUnauthorizedOrForbidden(): void
	{
		// Unauthorized if not logged in.
		$response = $this->getJsonWithData('Album::get', ['album_id' => $this->album1->id]);
		$response->assertUnauthorized();

		// Forbidden if logged in.
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::get', ['album_id' => $this->album1->id]);
		$response->assertForbidden();
	}
}