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

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumUpdateTest extends BaseApiV2Test
{
	public function testUpdateAlbumUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateAlbumAuthorized(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);
	}

	public function testUpdateTagAlbumUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'title',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'title',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateTagAlbumAuthorized(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'title',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => true,
				'is_model_album' => false,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => true,
			],
			'resource' => [
				'id' => $this->tagAlbum1->id,
				'title' => 'title', // from modified above.
				'photos' => [],
			],
		]);
		self::assertCount(0, $response->json('resource.photos'));
	}

	public function testUpdateProtectionPolicyUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateProtectionPolicyAuthorized(): void
	{
		// Set as public
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);

		$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);

		$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		// Check that album is indeed public
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertSee($this->album1->id);

		// Set as nsfw
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertJson([
			'albums' => [
				[
					'id' => $this->album1->id,
					'title' => $this->album1->title,
					'description' => null,
					'thumb' => [
						'id' => $this->photo1->id,
					],
					'is_nsfw' => true,
					'is_public' => true,
					'is_link_required' => false,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
		]);
		// Set as hidden
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => true,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => true,
			'is_password_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		// Check that album is indeed hidden
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertDontSee($this->album1->id);

		// Set with password
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'password' => 'something',
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => true,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
		]);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		// Check that album is indeed visible but locked
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertJson([
			'albums' => [
				[
					'id' => $this->album1->id,
					'title' => $this->album1->title,
					'description' => null,
					'thumb' => null,
					'is_nsfw' => false,
					'is_public' => true,
					'is_link_required' => false,
					'is_password_required' => true,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
		]);
	}
}