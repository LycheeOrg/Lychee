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

use App\Enum\PhotoHighlightVisibilityType;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoStarTest extends BaseApiWithDataTest
{
	public function testSetStarPhotoUnauthorizedForbidden(): void
	{
		// With anonymous visibility (and album1 not public), non-owning users cannot star
		Configs::set('photos_star_visibility', PhotoHighlightVisibilityType::EDITOR->value);

		$response = $this->postJson('Photo::highlight', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testSetStarPhotoAnonymous(): void
	{
		// With anonymous visibility (and album1 not public), non-owning users cannot star
		Configs::set('photos_star_visibility', PhotoHighlightVisibilityType::ANONYMOUS->value);

		$response = $this->postJson('Photo::highlight', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertNoContent($response);
	}

	public function testSetStarPhotoWithAuthenticatedVisibility(): void
	{
		Configs::set('photos_star_visibility', PhotoHighlightVisibilityType::AUTHENTICATED->value);

		$response = $this->postJson('Photo::highlight', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		// Under AUTHENTICATED visibility, any logged-in user can star
		$this->assertNoContent($response);
	}

	public function testSetStarPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photos' => [
				[
					'id' => $this->photo1->id,
					'is_highlighted' => true,
				],
			],
		]);
	}
}