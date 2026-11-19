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
use App\Actions\Search\StructOfArrays\QuerySearchPhotos;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\SmartAlbumType;
use App\Http\Resources\V3\SearchPhotoResource;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Optional;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I4 — the tier-2 photo query: the `search_result_limit` cap and
 * its `is_truncated` signal (S-069-09/10), once-per-request gate resolution
 * (S-069-16) and natural title ordering (S-069-21).
 */
class QuerySearchPhotosTest extends BaseApiWithDataTest
{
	private const TERM = 'CR_';

	private function run_query(?Album $origin = null, ?PhotoSortingCriterion $sorting = null): SearchPhotoResource
	{
		return resolve(QuerySearchPhotos::class)->do(
			SearchTokenParser::parse(self::TERM),
			$origin,
			Auth::user(),
			$sorting,
		);
	}

	public function tearDown(): void
	{
		Auth::logout();
		parent::tearDown();
	}

	// ── S-069-09 / S-069-10 — cap boundary ──────────────────────────

	public function testUnderTheCapEverythingIsReturnedAndNothingIsTruncated(): void
	{
		Configs::set('search_result_limit', '50');
		Auth::login($this->userMayUpload1);

		$result = $this->run_query();

		self::assertFalse($result->is_truncated);
		self::assertGreaterThan(1, count($result->ids));
	}

	public function testExactlyAtTheCapIsNotReportedAsTruncated(): void
	{
		Auth::login($this->userMayUpload1);
		$total = count($this->run_query()->ids);
		self::assertGreaterThan(1, $total, 'fixture must yield more than one match for this boundary test');

		Configs::set('search_result_limit', (string) $total);
		$result = $this->run_query();

		self::assertCount($total, $result->ids);
		self::assertFalse($result->is_truncated, 'a result of exactly `limit` rows is complete, not truncated');
	}

	public function testOverTheCapIsTruncatedToExactlyTheLimit(): void
	{
		Auth::login($this->userMayUpload1);
		$total = count($this->run_query()->ids);
		self::assertGreaterThan(1, $total);

		Configs::set('search_result_limit', (string) ($total - 1));
		$result = $this->run_query();

		self::assertCount($total - 1, $result->ids);
		self::assertTrue($result->is_truncated);
	}

	public function testEveryParallelArrayIsTruncatedTogether(): void
	{
		Auth::login($this->userMayUpload1);
		$total = count($this->run_query()->ids);
		Configs::set('search_result_limit', (string) ($total - 1));

		$result = $this->run_query();
		$expected = $total - 1;

		self::assertCount($expected, $result->ids);
		self::assertCount($expected, $result->album_ids);
		self::assertCount($expected, $result->titles);
		self::assertCount($expected, $result->ratios);
		self::assertCount($expected, $result->types);
		self::assertCount($expected, $result->created_ats);
	}

	// ── FR-069-04 — per-row album ids ───────────────────────────────

	public function testEveryRowCarriesAnAlbumId(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->run_query();

		self::assertCount(count($result->ids), $result->album_ids);
		foreach ($result->album_ids as $album_id) {
			self::assertNotNull($album_id);
			self::assertNotSame('', $album_id);
		}
	}

	/**
	 * Q-069-11: a photo the viewer owns but which sits in no album at all is
	 * searchable in v2 (`appendSearchabilityConditions()` ORs in
	 * `photos.owner_id = :uid`), so dropping it would be a silent membership
	 * change. It has no containing album to name, so it reports the `unsorted`
	 * smart album — which is exactly where the UI would navigate to find it,
	 * and which the v3 Asset endpoint already accepts.
	 */
	public function testAnUnsortedPhotoReportsTheUnsortedSmartAlbum(): void
	{
		Auth::login($this->userMayUpload1);

		$result = $this->run_query();

		$index = array_search($this->photoUnsorted->id, $result->ids, true);
		self::assertNotFalse($index, 'the owner must still find their own unsorted photo');
		self::assertSame(SmartAlbumType::UNSORTED->value, $result->album_ids[$index]);
	}

	// ── S-069-16 — gates resolved once per request ──────────────────

	public function testRatingFieldsAreOmittedEntirelyWhenRatingIsDisabled(): void
	{
		Configs::set('rating_enabled', '0');
		Auth::login($this->userMayUpload1);

		$result = $this->run_query();

		self::assertInstanceOf(Optional::class, $result->rating_avgs);
		self::assertInstanceOf(Optional::class, $result->rating_users);
	}

	public function testRatingFieldsArePresentWhenRatingIsEnabled(): void
	{
		Configs::set('rating_enabled', '1');
		Auth::login($this->userMayUpload1);

		$result = $this->run_query();

		self::assertIsArray($result->rating_avgs);
		self::assertCount(count($result->ids), $result->rating_avgs);
	}

	public function testTitlesAreBlankedForAGuestWhenFileNameHiddenIsOn(): void
	{
		Configs::set('file_name_hidden', '1');
		Configs::set('search_public', '1');

		// subAlbum4 is public, so a guest genuinely has something to match.
		$result = $this->run_query();

		foreach ($result->titles as $title) {
			self::assertSame('', $title);
		}
	}

	// ── S-069-21 — natural title ordering ───────────────────────────

	public function testTitleSortUsesNaturalOrderingAndUnambiguousColumns(): void
	{
		// `PhotoFactory` never populates Feature 060's derived `title_base`/
		// `title_index` columns (they are written explicitly at each real write
		// site, never by a model hook), so the shared fixture has no sort signal
		// at all. Set them here the way a real write site would — otherwise this
		// test would pass vacuously against six equal sort keys.
		$this->retitle($this->photo1, 'IMG_10');
		$this->retitle($this->photo1b, 'IMG_2');
		$this->retitle($this->subPhoto1, 'IMG_1');

		Auth::login($this->userMayUpload1);

		$result = resolve(QuerySearchPhotos::class)->do(
			SearchTokenParser::parse('IMG_'),
			null,
			Auth::user(),
			new PhotoSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC),
		);

		// Natural, not lexicographic: IMG_2 sorts before IMG_10.
		// That the query runs at all is the other half of the assertion —
		// `title_base`/`title_index` exist on `base_albums` too, and the policy
		// joins bring that table into scope, so an unqualified ORDER BY would
		// raise an ambiguous-column error (plan R2).
		self::assertSame(['IMG_1', 'IMG_2', 'IMG_10'], $result->titles);
	}

	private function retitle(\App\Models\Photo $photo, string $title): void
	{
		$split = \App\Services\TitleSplitter::split($title);
		$photo->title = $title;
		$photo->title_base = $split->base;
		$photo->title_index = $split->index;
		$photo->save();
	}
}
