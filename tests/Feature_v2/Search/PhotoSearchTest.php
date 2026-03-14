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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for token-based photo search (S-027-01 through S-027-22).
 *
 * These tests exercise the search endpoint with the new token grammar and verify that
 * each modifier correctly filters the photo result set.
 */
class PhotoSearchTest extends BaseApiWithDataTest
{
	// ---------------------------------------------------------------------------
	// Plain-text search
	// ---------------------------------------------------------------------------

	public function testPlainTextMatchesTitleReturnsPhoto(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode($this->photo1->title),
			]);
		$this->assertOk($response);
		$response->assertJsonPath('photos.0.id', $this->photo1->id);
	}

	public function testPlainTextNoMatchReturnsEmpty(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('__no_match_' . uniqid() . '__'),
			]);
		$this->assertOk($response);
		$response->assertJson(['photos' => []]);
	}

	public function testMultiplePlainTermsActAsAnd(): void
	{
		// Two terms that individually could match, but together they match nothing
		$part1 = substr($this->photo1->title, 0, 3);
		$part2 = '__nomatch_' . uniqid() . '__';
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode($part1 . ' ' . $part2),
			]);
		$this->assertOk($response);
		$response->assertJson(['photos' => []]);
	}

	// ---------------------------------------------------------------------------
	// Type modifier
	// ---------------------------------------------------------------------------

	public function testTypeModifierFiltersRecognisedMimeFragment(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('type:image'),
			]);
		$this->assertOk($response);

		// Any photo in the fixture with an image/* type should appear; just assert no crash
		$response->assertJsonStructure(['photos', 'albums']);
	}

	// ---------------------------------------------------------------------------
	// Invalid token — 422
	// ---------------------------------------------------------------------------

	public function testInvalidTokenReturnsUnprocessable(): void
	{
		// tag:* is explicitly forbidden
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:*'),
			]);
		$this->assertUnprocessable($response);
	}

	public function testInvalidDateTokenReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('date:not-a-date'),
			]);
		$this->assertUnprocessable($response);
	}

	public function testInvalidColourReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('colour:#xyz'),
			]);
		$this->assertUnprocessable($response);
	}

	public function testInvalidRatioReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:widescreen'),
			]);
		$this->assertUnprocessable($response);
	}

	public function testRatingWithoutOperatorReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:5'),
			]);
		$this->assertUnprocessable($response);
	}

	// ---------------------------------------------------------------------------
	// Ratio modifier (structural check only — seeded photos may lack size_variants)
	// ---------------------------------------------------------------------------

	public function testRatioLandscapeDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:landscape'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['photos', 'albums']);
	}

	// ---------------------------------------------------------------------------
	// EXIF field modifiers (structural smoke)
	// ---------------------------------------------------------------------------

	public function testMakeModifierDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('make:Canon'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['photos', 'albums']);
	}

	// ---------------------------------------------------------------------------
	// Colour modifier (structural smoke)
	// ---------------------------------------------------------------------------

	public function testColourHexDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('colour:#ff0000'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['photos', 'albums']);
	}

	public function testColourNamedCssColourDoesNotCrash(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('colour:red'),
			]);
		$this->assertOk($response);
		$response->assertJsonStructure(['photos', 'albums']);
	}

	public function testColourUnknownNameReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('colour:magentaPurple'),
			]);
		$this->assertUnprocessable($response);
	}

	// ---------------------------------------------------------------------------
	// Rating strategy
	// ---------------------------------------------------------------------------

	public function testRatingAvgGteMatchesPhotoAboveThreshold(): void
	{
		\Illuminate\Support\Facades\DB::table('photos')->where('id', $this->photo1->id)->update(['rating_avg' => 4.0]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:avg:>=3'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testRatingAvgGteBelowThresholdExcludesPhoto(): void
	{
		\Illuminate\Support\Facades\DB::table('photos')->where('id', $this->photo1->id)->update(['rating_avg' => 2.0]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:avg:>=3'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertNotContains($this->photo1->id, $ids->toArray());
	}

	public function testRatingOutOfRangeReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:avg:>=6'),
			]);
		$this->assertUnprocessable($response);
	}

	public function testRatingOwnGteMatchesAuthenticatedUserRating(): void
	{
		\App\Models\PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 4,
		]);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('rating:own:>=3'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	// ---------------------------------------------------------------------------
	// Ratio strategy — named buckets
	// ---------------------------------------------------------------------------

	public function testRatioLandscapeMatchesLandscapePhoto(): void
	{
		// Factory ORIGINAL size variant has ratio=1.5, which is > 1.05 (landscape).
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:landscape'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testRatioSquareDoesNotMatchLandscapePhoto(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:square'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertNotContains($this->photo1->id, $ids->toArray());
	}

	public function testRatioPortraitDoesNotMatchLandscapePhoto(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:portrait'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertNotContains($this->photo1->id, $ids->toArray());
	}

	// ---------------------------------------------------------------------------
	// Ratio strategy — numeric comparison
	// ---------------------------------------------------------------------------

	public function testRatioNumericGtMatchesLandscapePhoto(): void
	{
		// ratio:>1.0 — photo1 has ORIGINAL ratio=1.5
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:>1.0'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testRatioNumericLteExcludesLandscapePhoto(): void
	{
		// ratio:<=1.0 — photo1 ratio=1.5 should not match
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('ratio:<=1.0'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertNotContains($this->photo1->id, $ids->toArray());
	}

	// ---------------------------------------------------------------------------
	// Date strategy — exact match
	// ---------------------------------------------------------------------------

	public function testDateExactMatchesPhotoTakenOnDate(): void
	{
		$date = $this->photo1->taken_at->format('Y-m-d');

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('date:' . $date),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	// ---------------------------------------------------------------------------
	// Tag strategy
	// ---------------------------------------------------------------------------

	public function testTagExactMatchReturnsTaggedPhoto(): void
	{
		// photo1 has tag "test" (set up in BaseApiWithDataTest)
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:test'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testTagExactMatchExcludesUntaggedPhoto(): void
	{
		// photo1b has no tags
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:test'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertNotContains($this->photo1b->id, $ids->toArray());
	}

	public function testTagPrefixMatchReturnsTaggedPhoto(): void
	{
		// "tes*" prefix matches tag "test"
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:tes*'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testTagNoMatchReturnsEmptyPhotos(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('tag:__no_such_tag_' . uniqid() . '__'),
			]);
		$this->assertOk($response);
		$response->assertJson(['photos' => []]);
	}

	// ---------------------------------------------------------------------------
	// FieldLike strategy — prefix mode
	// ---------------------------------------------------------------------------

	public function testFieldLikePrefixMatchesPhoto(): void
	{
		// Factory default make='Canon'; 'Can*' prefix should match
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('make:Can*'),
			]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id');
		$this->assertContains($this->photo1->id, $ids->toArray());
	}

	public function testFieldLikePrefixNoMatchReturnsEmpty(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', [
				'album_id' => null,
				'terms' => base64_encode('make:XYZ' . uniqid() . '*'),
			]);
		$this->assertOk($response);
		$response->assertJson(['photos' => []]);
	}
}
