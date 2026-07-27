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

use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumUpdateTest extends BaseApiWithDataTest
{
	public function testUpdateAlbumUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
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
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
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
			'tags' => ['vacation', 'greece'],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);
		$this->assertEqualsCanonicalizing(['vacation', 'greece'], $response->json('tags'));

		$this->assertDatabaseHas('tags', ['name' => 'vacation']);
		$this->assertDatabaseHas('tags', ['name' => 'greece']);
	}

	/**
	 * The legacy v7 (PrimeVue) frontend does not know about album tags and
	 * never sends the `tags` key on `PATCH /Album`. Omitting the key entirely
	 * must leave existing album tags untouched, both for backward
	 * compatibility with v7 and so v7/v8 updates never race each other into
	 * silently wiping tags set from the other tree.
	 */
	public function testUpdateAlbumWithoutTagsKeyLeavesExistingTagsUntouched(): void
	{
		$this->album1->tags()->sync([Tag::factory()->with_name('vacation')->create()->id]);

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
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		$this->assertDatabaseHas('albums_tags', ['album_id' => $this->album1->id]);
		$this->assertEqualsCanonicalizing(['vacation'], $response->json('tags'));
	}

	public function testUpdateAlbumTagsCanBeCleared(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'tags' => ['vacation'],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);
		$this->assertDatabaseHas('albums_tags', ['album_id' => $this->album1->id]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'tags' => [],
		]);
		$this->assertDatabaseMissing('albums_tags', ['album_id' => $this->album1->id]);
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
			'is_pinned' => false,
			'is_and' => true,
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
			'is_pinned' => false,
			'is_and' => true,
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
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		// Verify album head metadata
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
				'title' => 'title', // from modified above.
				'show_tags' => ['tag1', 'tag2'],
			],
		]);

		// Verify photos are empty (tags changed, so photo1 no longer matches)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		self::assertCount(0, $response->json('photos'));
	}

	public function testUpdateAlbumIsPinned(): void
	{
		// Test setting album as pinned
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'Pinned Album',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => true,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		// Verify the album is pinned in the response
		$response->assertJson([
			'is_pinned' => true,
		]);

		// Test setting tag album as pinned
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'Pinned Tag Album',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => true,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		// Verify the tag album is pinned in the response
		$response->assertJson([
			'is_pinned' => true,
		]);

		// Test unpinning album
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'Unpinned Album',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		// Verify the album is unpinned in the response
		$response->assertJson([
			'is_pinned' => false,
		]);

		// Test unpinning tag album
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'Unpinned Tag Album',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		// Verify the tag album is unpinned in the response
		$response->assertJson([
			'is_pinned' => false,
		]);
	}

	public function testUpdateAlbumIsPinnedUnauthorized(): void
	{
		// Test unauthorized user attempting to pin an album
		$response = $this->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'Unauthorized Pinned Album',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => true,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertUnauthorized($response);

		// Test unauthorized user attempting to pin a tag album
		$response = $this->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'Unauthorized Pinned Tag Album',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => true,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertUnauthorized($response);

		// Test locked user attempting to pin an album
		$response = $this->actingAs($this->userLocked)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'Locked User Pinned Album',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => true,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertForbidden($response);

		// Test locked user attempting to pin a tag album
		$response = $this->actingAs($this->userLocked)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'Locked User Pinned Tag Album',
			'tags' => ['tag1', 'tag2'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => true,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateProtectionPolicyUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
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
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);

		$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);

		$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => 'unsorted',
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
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
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertSee($this->album1->id);
		$albums = $response->json('albums');
		$idx = array_search($this->album1->id, array_column($albums, 'id'), true);
		$response->assertJsonPath("albums.$idx.id", $this->album1->id);
		$response->assertJsonPath("albums.$idx.title", $this->album1->title);
		$response->assertJsonPath("albums.$idx.description", null);
		$response->assertJsonPath("albums.$idx.thumb.id", $this->photo1->id);
		$response->assertJsonPath("albums.$idx.is_nsfw", true);
		$response->assertJsonPath("albums.$idx.is_pinned", false);
		$response->assertJsonPath("albums.$idx.is_public", true);
		$response->assertJsonPath("albums.$idx.is_link_required", false);
		$response->assertJsonPath("albums.$idx.is_password_required", false);
		$response->assertJsonPath("albums.$idx.is_tag_album", false);
		$response->assertJsonPath("albums.$idx.has_subalbum", true);
		// Set as hidden
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => true,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => true,
			'is_password_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
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
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);
		$this->assertCreated($response);
		$response->assertJson([
			'is_public' => true,
			'is_link_required' => false,
			'is_password_required' => true,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]);

		// Logout.
		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		// Check that album is indeed visible but locked
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertSee($this->album1->id);
		$albums = $response->json('albums');
		$idx = array_search($this->album1->id, array_column($albums, 'id'), true);
		$response->assertJsonPath("albums.$idx.id", $this->album1->id);
		$response->assertJsonPath("albums.$idx.title", $this->album1->title);
		$response->assertJsonPath("albums.$idx.description", null);
		$response->assertJsonPath("albums.$idx.thumb.id", null);
		$response->assertJsonPath("albums.$idx.is_nsfw", false);
		$response->assertJsonPath("albums.$idx.is_pinned", false);
		$response->assertJsonPath("albums.$idx.is_public", true);
		$response->assertJsonPath("albums.$idx.is_link_required", false);
		$response->assertJsonPath("albums.$idx.is_password_required", true);
		$response->assertJsonPath("albums.$idx.is_tag_album", false);
		$response->assertJsonPath("albums.$idx.has_subalbum", true);
	}
}
