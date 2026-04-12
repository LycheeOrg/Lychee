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

namespace Tests\Feature_v2\TrustLevel;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for the Moderation API endpoints.
 */
class ModerationTest extends BaseApiWithDataTest
{
	public function testListRequiresAuthentication(): void
	{
		$response = $this->getJson('Moderation');
		$this->assertUnauthorized($response);
	}

	public function testListForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Moderation');
		$this->assertForbidden($response);
	}

	public function testListReturnsEmptyQueueByDefault(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Moderation');
		$this->assertOk($response);
		$response->assertJsonStructure(['photos', 'current_page', 'last_page', 'per_page', 'total']);
		$response->assertJsonPath('total', 0);
	}

	public function testListReturnsPendingPhotos(): void
	{
		// Set one of the existing photos to unvalidated
		$photo = $this->photo1;
		$photo->is_validated = false;
		$photo->save();

		try {
			$response = $this->actingAs($this->admin)->getJson('Moderation');
			$this->assertOk($response);
			$this->assertGreaterThanOrEqual(1, $response->json('total'));
			$response->assertJsonStructure([
				'photos' => [
					'*' => ['photo_id', 'title', 'owner_username', 'album_title', 'created_at'],
				],
			]);
		} finally {
			$photo->is_validated = true;
			$photo->save();
		}
	}

	public function testApproveRequiresAuthentication(): void
	{
		$response = $this->postJson('Moderation::approve', ['photo_ids' => [$this->photo1->id]]);
		$this->assertUnauthorized($response);
	}

	public function testApproveForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Moderation::approve', ['photo_ids' => [$this->photo1->id]]);
		$this->assertForbidden($response);
	}

	public function testApproveValidatesPhotoIds(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Moderation::approve', ['photo_ids' => []]);
		$this->assertUnprocessable($response);
	}

	public function testApproveMarkPhotosAsValidated(): void
	{
		$photo = $this->photo1;
		$photo->is_validated = false;
		$photo->save();

		try {
			$this->assertFalse($photo->is_validated);

			$response = $this->actingAs($this->admin)->postJson('Moderation::approve', ['photo_ids' => [$photo->id]]);
			$this->assertNoContent($response);

			$photo->refresh();
			$this->assertTrue($photo->is_validated);
		} finally {
			$photo->is_validated = true;
			$photo->save();
		}
	}

	public function testGetPhotoRequiresAuthentication(): void
	{
		$response = $this->getJson('Moderation::photo?photo_id=' . $this->photo1->id);
		$this->assertUnauthorized($response);
	}

	public function testGetPhotoForbiddenForNonAdmin(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Moderation::photo?photo_id=' . $this->photo1->id);
		$this->assertForbidden($response);
	}

	public function testGetPhotoRequiresPhotoId(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Moderation::photo');
		$this->assertUnprocessable($response);
	}

	public function testGetPhotoReturnsPhotoResource(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Moderation::photo?photo_id=' . $this->photo1->id);
		$this->assertOk($response);
		$response->assertJsonStructure(['id', 'title', 'type', 'size_variants']);
		$response->assertJsonPath('id', $this->photo1->id);
	}

	public function testGetPhotoWorksForUnvalidatedPhoto(): void
	{
		$photo = $this->photo1;
		$photo->is_validated = false;
		$photo->save();

		try {
			$response = $this->actingAs($this->admin)->getJson('Moderation::photo?photo_id=' . $photo->id);
			$this->assertOk($response);
			$response->assertJsonPath('id', $photo->id);
			$response->assertJsonPath('is_validated', false);
		} finally {
			$photo->is_validated = true;
			$photo->save();
		}
	}
}
