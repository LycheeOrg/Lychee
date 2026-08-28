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

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\SortingDecorator;
use App\Services\TitleSplitter;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-05, S-060-09): database-driven, natural-sort-like
 * Title ordering for albums - same behaviour as photos (S-060-01..06),
 * verified independently for `base_albums`.
 */
class AlbumTitleSortingTest extends BaseApiWithDataTest
{
	private function makeAlbum(string $title): Album
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->with_title($title)->create();
		$title_split = TitleSplitter::split($title);
		$album->title_base = $title_split->base;
		$album->title_index = $title_split->index;
		$album->save();

		return $album;
	}

	public function testTitleAscendingUsesNaturalOrder(): void
	{
		$a0 = $this->makeAlbum('test_0');
		$a1 = $this->makeAlbum('test_1');
		$a2 = $this->makeAlbum('test_2');
		$a10 = $this->makeAlbum('test_10');

		$query = BaseAlbumImpl::query()->whereIn('id', [$a0->id, $a1->id, $a2->id, $a10->id]);
		$results = (new SortingDecorator($query))->orderBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$a0->id, $a1->id, $a2->id, $a10->id], $results->pluck('id')->all());
	}

	public function testTitleDescendingUsesNaturalOrder(): void
	{
		$a0 = $this->makeAlbum('test_0');
		$a1 = $this->makeAlbum('test_1');
		$a2 = $this->makeAlbum('test_2');
		$a10 = $this->makeAlbum('test_10');

		$query = BaseAlbumImpl::query()->whereIn('id', [$a0->id, $a1->id, $a2->id, $a10->id]);
		$results = (new SortingDecorator($query))->orderBy(ColumnSortingType::TITLE, OrderSortingType::DESC)->get();

		$this->assertSame([$a10->id, $a2->id, $a1->id, $a0->id], $results->pluck('id')->all());
	}

	public function testParenthesisedNumberSortsNumerically(): void
	{
		$a2 = $this->makeAlbum('Album (2)');
		$a10 = $this->makeAlbum('Album (10)');

		$query = BaseAlbumImpl::query()->whereIn('id', [$a2->id, $a10->id]);
		$results = (new SortingDecorator($query))->orderBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$a2->id, $a10->id], $results->pluck('id')->all());
	}

	public function testNoDigitTitleSortsBeforeDigitSuffixedSibling(): void
	{
		$vacation = $this->makeAlbum('Vacation');
		$vacation5 = $this->makeAlbum('Vacation5');

		$query = BaseAlbumImpl::query()->whereIn('id', [$vacation->id, $vacation5->id]);
		$results = (new SortingDecorator($query))->orderBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$vacation->id, $vacation5->id], $results->pluck('id')->all());
	}

	public function testMixedCaseTitlesSortCaseInsensitively(): void
	{
		$apple = $this->makeAlbum('Apple');
		$banana = $this->makeAlbum('banana');
		$cherry = $this->makeAlbum('Cherry');

		$query = BaseAlbumImpl::query()->whereIn('id', [$apple->id, $banana->id, $cherry->id]);
		$results = (new SortingDecorator($query))->orderBy(ColumnSortingType::TITLE, OrderSortingType::ASC)->get();

		$this->assertSame([$apple->id, $banana->id, $cherry->id], $results->pluck('id')->all());
	}
}
