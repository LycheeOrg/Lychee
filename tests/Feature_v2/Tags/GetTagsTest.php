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

namespace Tests\Feature_v2\Tags;

use App\Models\AccessPermission;
use App\Models\Tag;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class GetTagsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		// The managed cache is gated behind this master switch (off by
		// default); enable it so the cache-hit assertions below actually
		// exercise the cache path instead of vacuously passing.
		config(['features.enable-caching' => true]);
	}

	public function testCacheHitPerformsNoAlbumsTableQuery(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);

		$this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);
		// Distinctive to `getAccessibleAlbums()`'s query shape — the sibling
		// `getAccessiblePhotos()` query also references "albums" (via its
		// NSFW-sensitivity subquery), so a blanket substring match on
		// "albums" would false-positive on that unrelated, intentionally
		// uncached query. Identifier quoting is driver-specific (SQLite/
		// Postgres use "double quotes", MySQL/MariaDB use `backticks`) — strip
		// both before matching so this works under any test DB driver.
		$album_query_count = count(array_filter(
			DB::getQueryLog(),
			fn (array $q) => str_contains(str_replace(['"', '`'], '', $q['query']), 'inner join base_albums on base_albums.id = albums.id')
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertSame(0, $album_query_count, 'A cache hit for the album half must not query albums/base_albums.');
		$this->assertCount(1, $response->json('albums'));
	}

	/**
	 * NFR-053-07/S-053-32: a session that has unlocked a password-protected
	 * album must never see a stale, locked view cached by another session
	 * (or the reverse).
	 */
	public function testDifferentUnlockStatesDoNotShareACacheEntry(): void
	{
		$password_album = $this->album2; // owned by userMayUpload2
		$password_album->tags()->sync([$this->tag_test->id]);
		AccessPermission::factory()->public()->visible()->locked()->for_album($password_album)->create();

		// Session A: never unlocks — should not see the password-protected album.
		$response_locked = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response_locked);
		$this->assertCount(0, $response_locked->json('albums'));

		// Session B (same user, different session state): unlocks the album
		// via the session-scoped unlock mechanism.
		session()->push(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, $password_album->id);
		$response_unlocked = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response_unlocked);
		$this->assertCount(1, $response_unlocked->json('albums'));
	}

	public function testGetTagGuest(): void
	{
		$response = $this->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetTagLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertCount(0, $data['photos']);
	}

	public function testGetTagUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertEquals($data['photos'][0]['id'], $this->photo1->id);
	}

	public function testGetTagUserWithShared(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertCount(0, $data['photos']);
	}

	public function testGetTagWithMissingTagId(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);
	}

	public function testGetNonExistentTag(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Tag', ['tag_id' => '999999']);
		$this->assertNotFound($response);
	}

	public function testGetTagIncludesAccessibleAlbums(): void
	{
		// tag_test is already attached to photo1; also attach it to album1 (same owner).
		$this->album1->tags()->sync([$this->tag_test->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('albums', $data);
		$this->assertIsArray($data['albums']);
		$this->assertCount(1, $data['albums']);
		$this->assertEquals($this->album1->id, $data['albums'][0]['id']);

		// photos are still returned as before.
		$this->assertCount(1, $data['photos']);
	}

	public function testGetTagAlbumsEmptyArrayWhenNoneAccessible(): void
	{
		// tag_test has photo1 but no album.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('albums', $data);
		$this->assertIsArray($data['albums']);
		$this->assertCount(0, $data['albums']);
	}

	public function testGetTagAlbumsScopedToAccessibleUser(): void
	{
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		// album2 is owned by userMayUpload2 and not shared with userMayUpload1
		// (unlike album1, which grants userMayUpload2 access via the `perm1` fixture).
		$this->album2->tags()->sync([$roadtrip->id]);

		// Owner sees the album.
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag', ['tag_id' => $roadtrip->id]);
		$this->assertOk($response);
		$this->assertCount(1, $response->json('albums'));

		// A different, unrelated non-admin does not see it.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $roadtrip->id]);
		$this->assertOk($response);
		$this->assertCount(0, $response->json('albums'));
	}
}
