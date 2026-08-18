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

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Tag;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for the /Album::albums endpoint's "matching albums" behaviour for
 * TagAlbum (albums carrying the criteria tag(s) as Feature-050 metadata) and
 * PersonAlbum (albums containing a photo matching the person criteria).
 */
class AlbumMatchingAlbumsTest extends BaseApiWithDataTest
{
	protected Person $person1;
	protected Person $person2;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');

		DB::table('persons')->delete();
		$this->person1 = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		$this->person2 = Person::factory()->create(['name' => 'Bob', 'is_searchable' => true]);
	}

	public function tearDown(): void
	{
		DB::table('person_albums_persons')->delete();
		DB::table('persons')->delete();

		parent::tearDown();
	}

	private function createPersonAlbum(array $person_ids, bool $is_and = false): string
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_' . implode('_', $person_ids),
			'persons' => $person_ids,
			'is_and' => $is_and,
		]);
		$this->assertOk($response);

		return $response->getOriginalContent();
	}

	// ── TagAlbum ──────────────────────────────────────────────────

	public function testTagAlbumMatchingAlbumsEmptyWhenNoAlbumTagged(): void
	{
		// tagAlbum1 is linked to `test`, but no real album carries `test` as metadata.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testTagAlbumMatchingAlbumsReturnsTaggedAlbum(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'data' => [['id' => $this->album1->id, 'title' => $this->album1->title]],
			'total' => 1,
		]);
	}

	public function testTagAlbumMatchingAlbumsHiddenWhenConfigDisabled(): void
	{
		Configs::set('TA_albums_listing_enabled', '0');
		$this->album1->tags()->sync([$this->tag_test->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testTagAlbumMatchingAlbumsDoesNotMatchOnPhotoTagAlone(): void
	{
		// photo1 already carries `test` (see BaseApiWithDataTest fixtures), but
		// album1 itself is not tagged — must NOT be returned (the whole point
		// of this feature is metadata tags, not derived-from-photo-tags).
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testTagAlbumMatchingAlbumsScopedToAccessibleUser(): void
	{
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		$tagAlbum = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$roadtrip])->create();

		// album2 is owned by userMayUpload2 and not shared with userMayUpload1.
		$this->album2->tags()->sync([$roadtrip->id]);

		// Owner of album2 can tag it, but the TagAlbum owner (userMayUpload1)
		// cannot see album2 in the matching-albums list.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $tagAlbum->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testTagAlbumMatchingAlbumsAndSemantics(): void
	{
		$second_tag = Tag::create(['name' => 'second']);
		$tagAlbum = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$this->tag_test, $second_tag])->create();
		$tagAlbum->is_and = true;
		$tagAlbum->save();

		// album1 only carries `test`, not `second` — must not match under AND.
		$this->album1->tags()->sync([$this->tag_test->id]);
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $tagAlbum->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);

		// Now album1 carries both — must match.
		$this->album1->tags()->sync([$this->tag_test->id, $second_tag->id]);
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $tagAlbum->id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [['id' => $this->album1->id]], 'total' => 1]);
	}

	// ── PersonAlbum ───────────────────────────────────────────────

	public function testPersonAlbumMatchingAlbumsEmptyWhenNoMatchingPhoto(): void
	{
		$album_id = $this->createPersonAlbum([$this->person1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testPersonAlbumMatchingAlbumsReturnsContainingAlbum(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		$album_id = $this->createPersonAlbum([$this->person1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$this->assertOk($response);
		$response->assertJson([
			'data' => [['id' => $this->album1->id, 'title' => $this->album1->title]],
			'total' => 1,
		]);
	}

	public function testPersonAlbumMatchingAlbumsAndSemantics(): void
	{
		// album1 has photo1 (person1 only) and photo1b (person2 only) — no
		// single photo has both, so AND must not match album1.
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo1b)->for_person($this->person2)->create();
		$album_id = $this->createPersonAlbum([$this->person1->id, $this->person2->id], is_and: true);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [], 'total' => 0]);

		// Now put both faces on the same photo — must match.
		Face::factory()->for_photo($this->photo1)->for_person($this->person2)->create();
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$this->assertOk($response);
		$response->assertJson(['data' => [['id' => $this->album1->id]], 'total' => 1]);
	}

	// ── Cache invalidation ────────────────────────────────────────

	public function testTagAlbumMatchingAlbumsReflectsNewlyTaggedAlbum(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_albums_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$response->assertJson(['data' => [], 'total' => 0]);

		// Reuses the existing AlbumTagsChanged invalidation path (no new
		// invalidation code was written for the TagAlbum side).
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => $this->album1->title,
			'license' => 'none',
			'description' => '',
			'tags' => [$this->tag_test->name],
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
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $this->tagAlbum1->id]);
		$response->assertJson(['data' => [['id' => $this->album1->id]], 'total' => 1]);
	}

	public function testPersonAlbumMatchingAlbumsReflectsFaceReassignment(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_albums_enabled', '1');

		$face = Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		$album_id = $this->createPersonAlbum([$this->person1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$response->assertJson(['data' => [['id' => $this->album1->id]], 'total' => 1]);

		// Reassign the face away from person1 — the cached "matching albums"
		// page for person1's PersonAlbum must be invalidated (PhotoPersonsChanged).
		$response = $this->actingAs($this->admin)->postJson('Face/' . $face->id . '/assign', [
			'person_id' => $this->person2->id,
		]);
		$this->assertStatus($response, [200, 201]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$response->assertJson(['data' => [], 'total' => 0]);
	}

	public function testPersonAlbumMatchingAlbumsReflectsPhotoMove(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_albums_enabled', '1');

		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		$album_id = $this->createPersonAlbum([$this->person1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$response->assertJson(['data' => [['id' => $this->album1->id]], 'total' => 1]);

		// Move photo1 into subAlbum1 (still owned by userMayUpload1) — the
		// cache must reflect the new containing album (PhotoMoved).
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::move', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->subAlbum1->id,
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::albums', ['album_id' => $album_id]);
		$response->assertJson(['data' => [['id' => $this->subAlbum1->id]], 'total' => 1]);
	}
}
