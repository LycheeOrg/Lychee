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

namespace Tests\Feature_v2\Photo;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Services\TitleSplitter;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-05): database-driven, natural-sort-like Title
 * ordering for photos. Covers S-060-01..06.
 */
class PhotoTitleSortingTest extends BaseApiWithDataTest
{
	private function makePhoto(string $title): Photo
	{
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->with_title($title)->create();
		$title_split = TitleSplitter::split($title);
		$photo->title_base = $title_split->base;
		$photo->title_index = $title_split->index;
		$photo->save();

		return $photo;
	}

	/**
	 * S-060-01: numeric-suffixed titles sort in natural (numeric) order
	 * ascending, not lexicographic.
	 */
	public function testTitleAscendingUsesNaturalOrder(): void
	{
		$p0 = $this->makePhoto('test_0');
		$p1 = $this->makePhoto('test_1');
		$p2 = $this->makePhoto('test_2');
		$p10 = $this->makePhoto('test_10');

		$query = Photo::query()->whereIn('id', [$p0->id, $p1->id, $p2->id, $p10->id]);
		$results = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$p0->id, $p1->id, $p2->id, $p10->id], $results->pluck('id')->all());
	}

	/**
	 * S-060-02: same set, descending.
	 */
	public function testTitleDescendingUsesNaturalOrder(): void
	{
		$p0 = $this->makePhoto('test_0');
		$p1 = $this->makePhoto('test_1');
		$p2 = $this->makePhoto('test_2');
		$p10 = $this->makePhoto('test_10');

		$query = Photo::query()->whereIn('id', [$p0->id, $p1->id, $p2->id, $p10->id]);
		$results = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::DESC)->get();

		$this->assertSame([$p10->id, $p2->id, $p1->id, $p0->id], $results->pluck('id')->all());
	}

	/**
	 * S-060-03: parenthesised-number fallback sorts numerically.
	 */
	public function testParenthesisedNumberSortsNumerically(): void
	{
		$p2 = $this->makePhoto('Photo (2)');
		$p10 = $this->makePhoto('Photo (10)');

		$query = Photo::query()->whereIn('id', [$p2->id, $p10->id]);
		$results = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$p2->id, $p10->id], $results->pluck('id')->all());
	}

	/**
	 * S-060-04: a title with no digit suffix (title_index defaults to 0)
	 * sorts immediately before a title with the same base plus a digit
	 * suffix, matching prior natural-sort behaviour.
	 */
	public function testNoDigitTitleSortsBeforeDigitSuffixedSibling(): void
	{
		$vacation = $this->makePhoto('Vacation');
		$vacation5 = $this->makePhoto('Vacation5');

		$query = Photo::query()->whereIn('id', [$vacation->id, $vacation5->id]);
		$results = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$vacation->id, $vacation5->id], $results->pluck('id')->all());
	}

	/**
	 * S-060-05: mixed-case titles sort case-insensitively.
	 */
	public function testMixedCaseTitlesSortCaseInsensitively(): void
	{
		$apple = $this->makePhoto('Apple');
		$banana = $this->makePhoto('banana');
		$cherry = $this->makePhoto('Cherry');

		$query = Photo::query()->whereIn('id', [$apple->id, $banana->id, $cherry->id]);
		$results = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$apple->id, $banana->id, $cherry->id], $results->pluck('id')->all());
	}

	/**
	 * S-060-06: paginated title-sorted listing - page 2's first item
	 * strictly follows page 1's last item, no PHP-level re-shuffle.
	 */
	public function testPaginatedTitleSortingIsStableAcrossPages(): void
	{
		$ids = [];
		foreach (range(0, 9) as $i) {
			$ids[] = $this->makePhoto('page_test_' . $i)->id;
		}

		$query = Photo::query()->whereIn('id', $ids);
		$page1 = (new SortingDecorator($query))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->paginate(5, ['*'], 'page', 1);

		$query2 = Photo::query()->whereIn('id', $ids);
		$page2 = (new SortingDecorator($query2))->orderPhotosBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->paginate(5, ['*'], 'page', 2);

		$expected = ['page_test_0', 'page_test_1', 'page_test_2', 'page_test_3', 'page_test_4', 'page_test_5', 'page_test_6', 'page_test_7', 'page_test_8', 'page_test_9'];
		$combined = array_merge($page1->items(), $page2->items());
		$this->assertSame($expected, array_map(fn (Photo $p) => $p->title, $combined));
	}
}
