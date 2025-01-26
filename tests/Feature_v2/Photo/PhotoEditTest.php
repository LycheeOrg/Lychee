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

use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiV2Test;

class PhotoEditTest extends BaseApiV2Test
{
	public function testEditPhotoUnauthorizedForbiddenUnprocessable(): void
	{
		$response = $this->patchJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', []);
		$this->assertUnprocessable($response);
	}

	public function testEditPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;

		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', false);

		// Test setting taken_at
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => '2021-01-01T00:00:00+00:00',
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;
		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.taken_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', true);

		// Reset taken_at
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;
		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', false);
	}

	public function testEditPhotoAuthorizedOwnerNullTakenDate(): void
	{
		DB::table('photos')->where('id', $this->photo1->id)->update(
			['taken_at' => null, 'initial_taken_at' => null, 'taken_at_orig_tz' => null, 'initial_taken_at_orig_tz' => null]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;

		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.taken_at', null);
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', false);

		// Test setting taken_at
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => '2021-01-01T00:00:00+00:00',
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;
		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.taken_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', true);

		// Reset taken_at
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'new title',
			'description' => 'new description',
			'tags' => ['tag1'],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$photo0 = $response->json('resource.photos.0');
		$idx = $photo0['id'] === $this->photo1->id ? 0 : 1;
		$response->assertJsonPath('resource.photos.' . $idx . '.id', $this->photo1->id);
		$response->assertJsonPath('resource.photos.' . $idx . '.title', 'new title');
		$response->assertJsonPath('resource.photos.' . $idx . '.description', 'new description');
		$response->assertJsonPath('resource.photos.' . $idx . '.tags', ['tag1']);
		$response->assertJsonPath('resource.photos.' . $idx . '.license', 'none');
		$response->assertJsonPath('resource.photos.' . $idx . '.created_at', '2021-01-01T00:00:00+00:00');
		$response->assertJsonPath('resource.photos.' . $idx . '.taken_at', null);
		$response->assertJsonPath('resource.photos.' . $idx . '.precomputed.is_taken_at_modified', false);
	}
}