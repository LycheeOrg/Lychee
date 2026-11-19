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

use App\Actions\Search\SearchTokenParser;
use App\Actions\Search\StructOfArrays\QuerySearchPhotoDetails;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Optional;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I6 — the tier-3 details query: silent curation of ids the caller
 * cannot see (S-069-19) and once-per-request EXIF/GPS/location gating
 * (S-069-17). The 300-id input cap is request validation and is covered at the
 * HTTP layer instead.
 */
class QuerySearchPhotoDetailsTest extends BaseApiWithDataTest
{
	private const TERM = 'CR_';

	/**
	 * @param string[] $photo_ids
	 */
	private function details(array $photo_ids): PhotoDetailResource
	{
		return resolve(QuerySearchPhotoDetails::class)->do(
			SearchTokenParser::parse(self::TERM),
			null,
			Auth::user(),
			$photo_ids,
		);
	}

	public function tearDown(): void
	{
		Auth::logout();
		parent::tearDown();
	}

	public function testResolvesTheRequestedVisiblePhoto(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->details([$this->photo1->id]);

		self::assertSame([$this->photo1->id], $result->ids);
		self::assertCount(1, $result->size_variants);
		self::assertCount(1, $result->tags);
		self::assertCount(1, $result->checksums);
	}

	// ── S-069-19 — invisible ids are curated, never an error ────────

	public function testAnIdTheCallerCannotSeeIsSilentlyAbsent(): void
	{
		Auth::login($this->userMayUpload1);

		// photo3 belongs to userNoUpload's unshared album3.
		$result = $this->details([$this->photo1->id, $this->photo3->id]);

		self::assertContains($this->photo1->id, $result->ids);
		self::assertNotContains($this->photo3->id, $result->ids);
	}

	public function testAnIdThatDoesNotMatchTheSearchTermIsSilentlyAbsent(): void
	{
		Auth::login($this->userMayUpload1);

		$result = resolve(QuerySearchPhotoDetails::class)->do(
			SearchTokenParser::parse('definitely-no-such-title-anywhere'),
			null,
			Auth::user(),
			[$this->photo1->id],
		);

		self::assertCount(0, $result->ids);
	}

	public function testAnUnknownIdIsSilentlyAbsent(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->details(['nonexistent-photo-id-00']);

		self::assertCount(0, $result->ids);
	}

	public function testAnEmptyIdListYieldsAnEmptyResult(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->details([]);

		self::assertCount(0, $result->ids);
	}

	// ── S-069-17 — EXIF / GPS / location gates ──────────────────────

	public function testExifFieldsAreOmittedEntirelyWhenDisplayExifDataIsOff(): void
	{
		Configs::set('display_exif_data', '0');
		Auth::login($this->userMayUpload1);

		$result = $this->details([$this->photo1->id]);

		self::assertInstanceOf(Optional::class, $result->makes);
		self::assertInstanceOf(Optional::class, $result->models);
		self::assertInstanceOf(Optional::class, $result->lenses);
	}

	public function testExifFieldsArePresentWhenDisplayExifDataIsOn(): void
	{
		Configs::set('display_exif_data', '1');
		Auth::login($this->userMayUpload1);

		$result = $this->details([$this->photo1->id]);

		self::assertIsArray($result->makes);
		self::assertCount(1, $result->makes);
	}

	public function testGpsFieldsAreOmittedEntirelyWhenCoordinateDisplayIsOff(): void
	{
		Configs::set('gps_coordinate_display', '0');
		Auth::login($this->userMayUpload1);

		$result = $this->details([$this->photo1->id]);

		self::assertInstanceOf(Optional::class, $result->latitudes);
		self::assertInstanceOf(Optional::class, $result->longitudes);
	}

	/**
	 * NFR-069-06: tier 2 + tier 3 together must reconstruct v2's
	 * `PhotoResource`. This asserts the tier-3 half carries the nested blocks
	 * that make that possible, rather than silently degrading them to null.
	 */
	public function testNestedBlocksAreResolvedNotNulled(): void
	{
		Configs::set('metrics_enabled', '1');
		Auth::login($this->userMayUpload1);

		$result = $this->details([$this->photo1->id]);

		self::assertNotNull($result->size_variants[0]);
		// photo1 is the fixture's palette-bearing photo.
		self::assertNotNull($result->palette[0]);
		self::assertIsArray($result->tags[0]);
	}
}
