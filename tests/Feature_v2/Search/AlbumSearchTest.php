<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Search;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\SearchTokenParser;
use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for token-based album search (S-027-23 / S-027-24).
 */
class AlbumSearchTest extends BaseApiWithDataTest
{
	// ---------------------------------------------------------------------------
	// Plain-text album search
	// ---------------------------------------------------------------------------

	public function testPlainTermMatchesAlbumTitle(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode($this->album1->title),
			]);
		$this->assertOk($response);
		$found = collect($response->json('albums'))->where('id', $this->album1->id);
		$this->assertTrue($found->isNotEmpty(), 'Expected album1 to appear in album results.');
	}

	public function testPlainTermNoMatchReturnsNoAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('__no_matching_album_' . uniqid() . '__'),
			]);
		$this->assertOk($response);
		$response->assertJson(['albums' => []]);
	}

	// ---------------------------------------------------------------------------
	// Title modifier
	// ---------------------------------------------------------------------------

	public function testTitleModifierMatchesAlbumTitle(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('title:' . $this->album1->title),
			]);
		$this->assertOk($response);
		$found = collect($response->json('albums'))->where('id', $this->album1->id);
		$this->assertTrue($found->isNotEmpty(), 'Expected album1 to appear with title: modifier.');
	}

	// ---------------------------------------------------------------------------
	// Description modifier (smoke)
	// ---------------------------------------------------------------------------

	public function testDescriptionModifierDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('description:gallery'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['albums', 'photos']);
	}

	// ---------------------------------------------------------------------------
	// Date modifier (smoke)
	// ---------------------------------------------------------------------------

	public function testDateModifierDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('date:>=2020-01-01'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['albums', 'photos']);
	}

	// ---------------------------------------------------------------------------
	// Invalid date token still returns 422 (shared validation)
	// ---------------------------------------------------------------------------

	public function testInvalidDateModifierReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('date:not-a-date'),
			]);
		$this->assertUnprocessable($response);
	}

	// ---------------------------------------------------------------------------
	// Photo-only modifiers must not leak into album results
	// ---------------------------------------------------------------------------

	public function testRatingOnlyTokenReturnsNoAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:avg:>=1'),
			]);
		$this->assertOk($response);
		$response->assertJson(['albums' => []]);
	}

	public function testTagModifierWithNoMatchingAlbumReturnsNoAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:__no_matching_album_tag_' . uniqid() . '__'),
			]);
		$this->assertOk($response);
		$response->assertJson(['albums' => []]);
	}

	// ---------------------------------------------------------------------------
	// Album tag matching (Feature 050 - Album Tags)
	// ---------------------------------------------------------------------------

	public function testTagModifierMatchesAlbumWithOwnTag(): void
	{
		$this->album1->tags()->sync([Tag::factory()->with_name('vacation')->create()->id]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:vacation'),
			]);
		$this->assertOk($response);
		$found = collect($response->json('albums'))->where('id', $this->album1->id);
		$this->assertTrue($found->isNotEmpty(), 'Expected album1 to appear via its own tag: modifier.');
	}

	public function testTagModifierDoesNotMatchTagAlbumsOwnCriteriaTags(): void
	{
		$this->be($this->userMayUpload1);

		// tagAlbum1 is linked (via tag_albums_tags) to tag_test, i.e. its matching
		// criteria is "test". A `tag:test` query against queryAlbums() must never
		// pick up tagAlbum1 via its own (unrelated) tags() relation.
		$tokens = SearchTokenParser::parse('tag:' . $this->tag_test->name);
		$results = app(AlbumSearch::class)->queryAlbums($tokens);

		$this->assertNotContains($this->tagAlbum1->id, $results->pluck('id')->toArray());
	}

	public function testTagModifierHasNoEffectOnQueryTagAlbums(): void
	{
		$this->be($this->userMayUpload1);

		// Regression guard for NFR-050-01: `tag:` must never be wired into the
		// TagAlbum search registry. tagAlbum1 does not match "test" by title,
		// so if `tag:` leaked in and matched via tags(), this would wrongly
		// return tagAlbum1.
		$tokens = SearchTokenParser::parse('tag:' . $this->tag_test->name);
		$results = app(AlbumSearch::class)->queryTagAlbums($tokens);

		$this->assertEmpty($results);
	}

	public function testPlainTextMatchesAlbumOwnTag(): void
	{
		$this->album1->tags()->sync([Tag::factory()->with_name('greece')->create()->id]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('greece'),
			]);
		$this->assertOk($response);
		$found = collect($response->json('albums'))->where('id', $this->album1->id);
		$this->assertTrue($found->isNotEmpty(), 'Expected album1 to appear via plain-text tag match.');
	}

	// ---------------------------------------------------------------------------
	// queryTagAlbums (service-layer direct tests)
	// ---------------------------------------------------------------------------

	public function testQueryTagAlbumsMatchesByTitle(): void
	{
		$this->be($this->userMayUpload1);

		$tokens = SearchTokenParser::parse($this->tagAlbum1->title);
		$results = app(AlbumSearch::class)->queryTagAlbums($tokens);

		$ids = $results->pluck('id');
		$this->assertContains($this->tagAlbum1->id, $ids->toArray());
	}

	public function testQueryTagAlbumsNoMatchReturnsEmptyCollection(): void
	{
		$this->be($this->userMayUpload1);

		$tokens = SearchTokenParser::parse('__no_matching_tag_album_' . uniqid() . '__');
		$results = app(AlbumSearch::class)->queryTagAlbums($tokens);

		$this->assertEmpty($results);
	}
}
