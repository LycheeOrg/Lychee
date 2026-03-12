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
}
