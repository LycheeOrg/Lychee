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

namespace Tests\Feature_v3\Search;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I7 — route gating (S-069-02/03/04) and parameter validation
 * (S-069-05/06/07) across all four v3 search routes.
 */
class SearchV3GatingTest extends BaseApiWithDataTest
{
	private const ROUTES = [
		'Search/Photos',
		'Search/albums',
		'Search/albums/rights',
	];

	private function terms(string $raw): string
	{
		return base64_encode($raw);
	}

	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.struct-of-array', true);
	}

	// ── S-069-04 — feature flag ─────────────────────────────────────

	public function testEveryRouteIsForbiddenWhenTheFeatureFlagIsOff(): void
	{
		Config::set('features.struct-of-array', false);
		// Authenticated on purpose: an anonymous caller would be rejected by the
		// auth gate (401) before the flag gate is ever reached, which would not
		// prove anything about the flag.
		$this->actingAs($this->userMayUpload1);

		foreach (self::ROUTES as $route) {
			$response = $this->getJsonV3($route, ['terms' => $this->terms('CR_')]);
			$this->assertForbidden($response);
		}

		$response = $this->getJsonV3('Search/Photos/details', [
			'terms' => $this->terms('CR_'),
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testV2SearchStillWorksWhenTheV3FlagIsOff(): void
	{
		Config::set('features.struct-of-array', false);

		$response = $this->actingAs($this->userMayUpload1)
			->getJsonWithData('Search', ['album_id' => null, 'terms' => $this->terms('CR_')]);

		$this->assertOk($response);
	}

	// ── S-069-02 / S-069-03 — guest gating ──────────────────────────

	/**
	 * An anonymous caller denied by `search_public` gets 401, not 403 — Lychee
	 * reports "you are not authenticated" rather than "you may not do this" for
	 * a guest, exactly as v2's own `SearchTest` asserts.
	 */
	public function testGuestIsRejectedWhenSearchPublicIsOff(): void
	{
		Configs::set('search_public', '0');

		foreach (self::ROUTES as $route) {
			$response = $this->getJsonV3($route, ['terms' => $this->terms('CR_')]);
			$this->assertUnauthorized($response);
		}
	}

	public function testGuestIsAllowedWhenSearchPublicIsOn(): void
	{
		Configs::set('search_public', '1');

		foreach (self::ROUTES as $route) {
			$response = $this->getJsonV3($route, ['terms' => $this->terms('CR_')]);
			$this->assertOk($response);
		}
	}

	public function testAuthenticatedUserIsAllowedRegardlessOfSearchPublic(): void
	{
		Configs::set('search_public', '0');
		$this->actingAs($this->userMayUpload1);

		foreach (self::ROUTES as $route) {
			$response = $this->getJsonV3($route, ['terms' => $this->terms('CR_')]);
			$this->assertOk($response);
		}
	}

	// ── S-069-05 / S-069-06 / S-069-07 — validation ─────────────────

	public function testMissingTermsIs422(): void
	{
		$this->actingAs($this->userMayUpload1);

		foreach (self::ROUTES as $route) {
			$response = $this->getJsonV3($route, []);
			$this->assertUnprocessable($response);
		}
	}

	public function testUndecodableTermsIs422(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos', ['terms' => 'not!valid!base64!']);

		$this->assertUnprocessable($response);
	}

	public function testInvalidSortingColumnIs422(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms('CR_'),
			'sorting_column' => 'not_a_real_column',
			'sorting_order' => 'ASC',
		]);

		$this->assertUnprocessable($response);
	}

	public function testInvalidSortingOrderIs422(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms('CR_'),
			'sorting_column' => 'title',
			'sorting_order' => 'sideways',
		]);

		$this->assertUnprocessable($response);
	}

	public function testSortingColumnWithoutSortingOrderIs422(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms('CR_'),
			'sorting_column' => 'title',
		]);

		$this->assertUnprocessable($response);
	}

	// ── S-069-18 — details id cap ───────────────────────────────────

	public function testDetailsAcceptsExactly300Ids(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos/details', [
			'terms' => $this->terms('CR_'),
			'photo_ids' => array_fill(0, 300, $this->photo1->id),
		]);

		$this->assertOk($response);
	}

	public function testDetailsRejects301Ids(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos/details', [
			'terms' => $this->terms('CR_'),
			'photo_ids' => array_fill(0, 301, $this->photo1->id),
		]);

		$this->assertUnprocessable($response);
	}

	public function testDetailsRequiresPhotoIds(): void
	{
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonV3('Search/Photos/details', ['terms' => $this->terms('CR_')]);

		$this->assertUnprocessable($response);
	}

	// ── S-069-13 — smart album origin is treated as no origin ───────

	public function testASmartAlbumOriginIsAcceptedAndTreatedAsNoOrigin(): void
	{
		$this->actingAs($this->userMayUpload1);

		$scoped = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms($this->photo1->title),
			'album_id' => $this->tagAlbum1->id,
		]);
		$unscoped = $this->getJsonV3('Search/Photos', ['terms' => $this->terms($this->photo1->title)]);

		$this->assertOk($scoped);
		$this->assertOk($unscoped);
		self::assertSame($unscoped->json('ids'), $scoped->json('ids'));
	}

	public function testARealAlbumOriginNarrowsTheResult(): void
	{
		$this->actingAs($this->userMayUpload1);

		// subAlbum1 is inside album1 but does not contain photo1, and
		// userMayUpload1 can access it — an origin they cannot access would be
		// rejected by the CAN_ACCESS gate before scoping ever came into play.
		$response = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms($this->photo1->title),
			'album_id' => $this->subAlbum1->id,
		]);

		$this->assertOk($response);
		self::assertNotContains($this->photo1->id, $response->json('ids'));
	}

	public function testAnOriginTheCallerCannotAccessIsRejected(): void
	{
		$this->actingAs($this->userMayUpload1);

		// album3 belongs to userNoUpload and is shared with nobody.
		$response = $this->getJsonV3('Search/Photos', [
			'terms' => $this->terms('CR_'),
			'album_id' => $this->album3->id,
		]);

		$this->assertForbidden($response);
	}
}
