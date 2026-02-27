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

namespace Tests\Unit\Http;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSE;

/**
 * Tests for album slug CRUD operations:
 * setting, clearing, validation (format, reserved, duplicate).
 */
class AlbumSlugCrudTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use RequireSE;

	private const API_PREFIX = '/api/v2/';

	private User $admin;
	private User $userMayUpload1;
	private User $userMayUpload2;
	private User $userLocked;
	private Album $album1;
	private Album $album2;
	private TagAlbum $tagAlbum1;

	protected function setUp(): void
	{
		parent::setUp();

		$this->requireSe();

		$this->admin = User::factory()->may_administrate()->create();
		$this->userMayUpload1 = User::factory()->may_upload()->create();
		$this->userMayUpload2 = User::factory()->may_upload()->create();
		$this->userLocked = User::factory()->locked()->create();

		$this->album1 = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->album2 = Album::factory()->as_root()->owned_by($this->userMayUpload2)->create();

		$tag_test = Tag::factory()->with_name('test')->create();
		$this->tagAlbum1 = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$tag_test])->create();

		Configs::set('owner_id', $this->admin->id);

		$this->withoutVite();
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	private function apiGetJson(string $uri, array $data = []): TestResponse
	{
		return $this->withCredentials()->json('GET', self::API_PREFIX . $uri, $data);
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	private function apiPatchJson(string $uri, array $data = []): TestResponse
	{
		return $this->withCredentials()->json('PATCH', self::API_PREFIX . $uri, $data);
	}

	/**
	 * Test setting a slug on an album via PATCH /Album.
	 */
	public function testSetSlugOnAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'my-vacation',
		]);
		$this->assertOk($response);

		// Verify slug is persisted
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album1->id,
				'slug' => 'my-vacation',
			],
		]);
	}

	/**
	 * Test clearing a slug by sending null.
	 */
	public function testClearSlugOnAlbum(): void
	{
		// First, set a slug
		DB::table('base_albums')
			->where('id', '=', $this->album1->id)
			->update(['slug' => 'temporary-slug']);

		// Clear it via update
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => null,
		]);
		$this->assertOk($response);

		// Verify slug is cleared
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album1->id,
				'slug' => null,
			],
		]);
	}

	/**
	 * Test that a duplicate slug is rejected.
	 */
	public function testDuplicateSlugRejected(): void
	{
		// Set a slug on album2
		DB::table('base_albums')
			->where('id', '=', $this->album2->id)
			->update(['slug' => 'taken-slug']);

		// Try to set the same slug on album1
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'taken-slug',
		]);
		$this->assertUnprocessable($response);
	}

	/**
	 * Test that a reserved slug (SmartAlbumType) is rejected.
	 */
	public function testReservedSlugRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'unsorted',
		]);
		$this->assertUnprocessable($response);
	}

	/**
	 * Test that an invalid format slug is rejected.
	 */
	public function testInvalidFormatSlugRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'A',
		]);
		$this->assertUnprocessable($response);
	}

	/**
	 * Test unauthorized user cannot set slug.
	 */
	public function testSetSlugUnauthorized(): void
	{
		$response = $this->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'my-slug',
		]);
		$this->assertUnauthorized($response);
	}

	/**
	 * Test forbidden user cannot set slug.
	 */
	public function testSetSlugForbidden(): void
	{
		$response = $this->actingAs($this->userLocked)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'my-slug',
		]);
		$this->assertForbidden($response);
	}

	/**
	 * Test setting a slug on a tag album.
	 */
	public function testSetSlugOnTagAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => $this->tagAlbum1->title,
			'tags' => ['test'],
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => false,
			'photo_layout' => null,
			'photo_timeline' => null,
			'slug' => 'my-tag-album',
		]);
		$this->assertOk($response);

		// Verify slug is persisted
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->tagAlbum1->id,
				'slug' => 'my-tag-album',
			],
		]);
	}

	/**
	 * Test that an album can keep its own slug when re-saving.
	 */
	public function testSameSlugOnSameAlbumAllowed(): void
	{
		// Set initial slug
		DB::table('base_albums')
			->where('id', '=', $this->album1->id)
			->update(['slug' => 'my-existing-slug']);

		// Update album with same slug (should succeed — exclude_album_id logic)
		$response = $this->actingAs($this->userMayUpload1)->apiPatchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'album_sorting_column' => null,
			'album_sorting_order' => null,
			'album_aspect_ratio' => null,
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'slug' => 'my-existing-slug',
		]);
		$this->assertOk($response);
	}
}
