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
use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for the search page's sort column/direction controls.
 */
class SearchSortingTest extends BaseApiWithDataTest
{
	private const PREFIX = 'PSort_';

	/**
	 * Creates three photos in $this->album1, all matching a common plain-text
	 * term, but with distinct title/created_at/taken_at so ordering is
	 * unambiguous regardless of which column is used.
	 *
	 * @return array{0:Photo,1:Photo,2:Photo} [$aPhoto, $bPhoto, $cPhoto] where
	 *                                        title/created_at/taken_at ordering all agree: a < b < c
	 */
	private function makeOrderedPhotos(): array
	{
		$a = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create([
			'title' => self::PREFIX . 'Apple',
			'created_at' => Carbon::parse('2020-01-01'),
			'taken_at' => Carbon::parse('2020-06-01'),
		]);
		$b = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create([
			'title' => self::PREFIX . 'Banana',
			'created_at' => Carbon::parse('2021-01-01'),
			'taken_at' => Carbon::parse('2021-06-01'),
		]);
		$c = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create([
			'title' => self::PREFIX . 'Cherry',
			'created_at' => Carbon::parse('2022-01-01'),
			'taken_at' => Carbon::parse('2022-06-01'),
		]);

		return [$a, $b, $c];
	}

	private function searchPhotoIds(string $term, ?string $order_by = null, ?string $order_dir = null): array
	{
		$data = ['album_id' => null, 'terms' => base64_encode($term)];
		if ($order_by !== null) {
			$data['sorting_column'] = $order_by;
		}
		if ($order_dir !== null) {
			$data['sorting_order'] = $order_dir;
		}

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', $data);
		$this->assertOk($response);

		return collect($response->json('photos'))->pluck('id')->toArray();
	}

	// ---------------------------------------------------------------------------
	// Photo ordering
	// ---------------------------------------------------------------------------

	public function testTitleAscendingOrdersLexicographically(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'title', 'ASC');

		$this->assertSame([$a->id, $b->id, $c->id], $ids);
	}

	public function testTitleDescendingReversesLexicographicOrder(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'title', 'DESC');

		$this->assertSame([$c->id, $b->id, $a->id], $ids);
	}

	public function testCreatedAtAscendingOrdersOldestFirst(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'created_at', 'ASC');

		$this->assertSame([$a->id, $b->id, $c->id], $ids);
	}

	public function testCreatedAtDescendingOrdersNewestFirst(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'created_at', 'DESC');

		$this->assertSame([$c->id, $b->id, $a->id], $ids);
	}

	public function testTakenAtAscendingOrdersOldestFirst(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'taken_at', 'ASC');

		$this->assertSame([$a->id, $b->id, $c->id], $ids);
	}

	public function testTakenAtDescendingOrdersNewestFirst(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX, 'taken_at', 'DESC');

		$this->assertSame([$c->id, $b->id, $a->id], $ids);
	}

	public function testNoSortingParamsKeepsDefaultTakenAtAscending(): void
	{
		[$a, $b, $c] = $this->makeOrderedPhotos();

		$ids = $this->searchPhotoIds(self::PREFIX);

		$this->assertSame([$a->id, $b->id, $c->id], $ids);
	}

	// ---------------------------------------------------------------------------
	// Validation
	// ---------------------------------------------------------------------------

	public function testInvalidSortingColumnReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', [
			'album_id' => null,
			'terms' => base64_encode('something'),
			'sorting_column' => 'not_a_real_column',
			'sorting_order' => 'ASC',
		]);
		$this->assertUnprocessable($response);
	}

	public function testInvalidSortingOrderReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', [
			'album_id' => null,
			'terms' => base64_encode('something'),
			'sorting_column' => 'title',
			'sorting_order' => 'sideways',
		]);
		$this->assertUnprocessable($response);
	}

	public function testSortingColumnWithoutOrderReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', [
			'album_id' => null,
			'terms' => base64_encode('something'),
			'sorting_column' => 'title',
		]);
		$this->assertUnprocessable($response);
	}

	// ---------------------------------------------------------------------------
	// Album ordering — taken_at depends on direction (max_taken_at for DESC,
	// min_taken_at for ASC). Tested at the Action layer directly (bypassing the
	// base64/HTTP round trip) for precise control over the computed columns,
	// matching AlbumSearchTest's existing direct-Action-call convention.
	// ---------------------------------------------------------------------------

	public function testAlbumTakenAtDescendingUsesMaxTakenAt(): void
	{
		$this->be($this->userMayUpload1);

		// Overlapping ranges, deliberately chosen so that ordering by max_taken_at
		// and min_taken_at produce opposite results.
		$early = Album::factory()->as_root()->with_title('ASort_Early')->owned_by($this->userMayUpload1)->create();
		$late = Album::factory()->as_root()->with_title('ASort_Late')->owned_by($this->userMayUpload1)->create();

		DB::table('albums')->where('id', $early->id)->update([
			'min_taken_at' => Carbon::parse('2020-01-01'),
			'max_taken_at' => Carbon::parse('2020-06-01'),
		]);
		DB::table('albums')->where('id', $late->id)->update([
			'min_taken_at' => Carbon::parse('2020-02-01'),
			'max_taken_at' => Carbon::parse('2020-12-01'),
		]);

		$tokens = SearchTokenParser::parse('ASort_');
		$sorting = new AlbumSortingCriterion(ColumnSortingType::MAX_TAKEN_AT, OrderSortingType::DESC);
		$ids = app(AlbumSearch::class)->queryAlbums($tokens, null, $sorting)->pluck('id')->toArray();

		$this->assertSame([$late->id, $early->id], array_values(array_intersect($ids, [$late->id, $early->id])));
	}

	public function testAlbumTakenAtAscendingUsesMinTakenAt(): void
	{
		$this->be($this->userMayUpload1);

		$early = Album::factory()->as_root()->with_title('ASort_Early')->owned_by($this->userMayUpload1)->create();
		$late = Album::factory()->as_root()->with_title('ASort_Late')->owned_by($this->userMayUpload1)->create();

		DB::table('albums')->where('id', $early->id)->update([
			'min_taken_at' => Carbon::parse('2020-01-01'),
			'max_taken_at' => Carbon::parse('2020-06-01'),
		]);
		DB::table('albums')->where('id', $late->id)->update([
			'min_taken_at' => Carbon::parse('2020-02-01'),
			'max_taken_at' => Carbon::parse('2020-12-01'),
		]);

		$tokens = SearchTokenParser::parse('ASort_');
		$sorting = new AlbumSortingCriterion(ColumnSortingType::MIN_TAKEN_AT, OrderSortingType::ASC);
		$ids = app(AlbumSearch::class)->queryAlbums($tokens, null, $sorting)->pluck('id')->toArray();

		$this->assertSame([$early->id, $late->id], array_values(array_intersect($ids, [$early->id, $late->id])));
	}

	public function testAlbumTitleAscendingOrdersLexicographically(): void
	{
		$this->be($this->userMayUpload1);

		$a = Album::factory()->as_root()->with_title('ASort_Alpha')->owned_by($this->userMayUpload1)->create();
		$z = Album::factory()->as_root()->with_title('ASort_Zulu')->owned_by($this->userMayUpload1)->create();

		$tokens = SearchTokenParser::parse('ASort_');
		$sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE_STRICT, OrderSortingType::ASC);
		$ids = app(AlbumSearch::class)->queryAlbums($tokens, null, $sorting)->pluck('id')->toArray();

		$this->assertSame([$a->id, $z->id], array_values(array_intersect($ids, [$a->id, $z->id])));
	}
}
