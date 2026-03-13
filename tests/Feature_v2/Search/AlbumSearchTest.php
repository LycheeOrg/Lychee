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
