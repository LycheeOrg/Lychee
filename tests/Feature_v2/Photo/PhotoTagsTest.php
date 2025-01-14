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

class PhotoTagsTest extends BaseApiV2Test
{
	public function testTagsPhotoUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Photo::tags', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testTagsPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => true,
		]);
		$this->assertNoContent($response);
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'tags' => ['tag1'],
					],
				],
			],
		]);
	}

	public function testTagsPhotoAuthorizedOwnerKeepUniqueness(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => false,
		]);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'tags' => ['tag1'],
					],
				],
			],
		]);
	}

	public function testTagsPhotoAuthorizedOwnerPreserveNoOverride(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1', 'tag2'],
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => false,
		]);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'tags' => ['tag1', 'tag2'],
					],
				],
			],
		]);
	}

	public function testTagsPhotoAuthorizedOwnerExtendNoOverride(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1'],
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag2'],
			'shall_override' => false,
		]);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'tags' => ['tag1', 'tag2'],
					],
				],
			],
		]);
	}

	public function testTagsPhotoAuthorizedOwnerOverride(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag1', 'tag2'],
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo1->id],
			'tags' => ['tag3'],
			'shall_override' => true,
		]);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'tags' => ['tag3'],
					],
				],
			],
		]);
	}
}