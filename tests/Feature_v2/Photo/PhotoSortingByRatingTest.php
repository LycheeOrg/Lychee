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

use App\Enum\ColumnSortingPhotoType;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for sorting photos by rating_avg (Feature 009, FR-009-02).
 */
class PhotoSortingByRatingTest extends BaseApiWithDataTest
{
	/**
	 * Test that ColumnSortingPhotoType::RATING_AVG exists.
	 */
	public function testRatingAvgEnumExists(): void
	{
		$this->assertNotNull(ColumnSortingPhotoType::RATING_AVG);
		$this->assertEquals('rating_avg', ColumnSortingPhotoType::RATING_AVG->value);
	}

	/**
	 * Test that ColumnSortingType::RATING_AVG exists.
	 */
	public function testRatingAvgSortingTypeExists(): void
	{
		$this->assertNotNull(ColumnSortingType::RATING_AVG);
		$this->assertEquals('rating_avg', ColumnSortingType::RATING_AVG->value);
	}

	/**
	 * Test that ColumnSortingPhotoType converts to ColumnSortingType correctly.
	 */
	public function testRatingAvgEnumConverts(): void
	{
		$sortingType = ColumnSortingPhotoType::RATING_AVG->toColumnSortingType();
		$this->assertEquals(ColumnSortingType::RATING_AVG, $sortingType);
	}

	/**
	 * Test that rating_avg requires raw ordering.
	 */
	public function testRatingAvgRequiresRawOrdering(): void
	{
		$this->assertTrue(ColumnSortingType::RATING_AVG->requiresRawOrdering());
		$this->assertFalse(ColumnSortingType::CREATED_AT->requiresRawOrdering());
	}

	/**
	 * Test that rating_avg raw expression uses COALESCE.
	 */
	public function testRatingAvgRawExpression(): void
	{
		$expression = ColumnSortingType::RATING_AVG->getRawOrderExpression('');
		$this->assertEquals('COALESCE(rating_avg, -1)', $expression);

		$expressionWithPrefix = ColumnSortingType::RATING_AVG->getRawOrderExpression('photos.');
		$this->assertEquals('COALESCE(photos.rating_avg, -1)', $expressionWithPrefix);
	}

	/**
	 * Test sorting photos by rating DESC with NULLs last.
	 */
	public function testSortingByRatingDescNullsLast(): void
	{
		// Set up photos with different ratings
		$this->photo1->rating_avg = '5.0000';
		$this->photo1->save();

		$this->photo1b->rating_avg = '3.0000';
		$this->photo1b->save();

		$this->subPhoto1->rating_avg = null; // Unrated
		$this->subPhoto1->save();

		// Create query with sorting
		$query = Photo::query()->whereIn('id', [
			$this->photo1->id,
			$this->photo1b->id,
			$this->subPhoto1->id,
		]);

		$results = (new SortingDecorator($query))
			->orderPhotosBy(ColumnSortingType::RATING_AVG, OrderSortingType::DESC)
			->get();

		// Verify order: 5.0 first, 3.0 second, NULL last
		$this->assertCount(3, $results);
		$this->assertEquals($this->photo1->id, $results[0]->id); // 5.0
		$this->assertEquals($this->photo1b->id, $results[1]->id); // 3.0
		$this->assertEquals($this->subPhoto1->id, $results[2]->id); // NULL
	}

	/**
	 * Test sorting photos by rating ASC with NULLs last.
	 */
	public function testSortingByRatingAscNullsLast(): void
	{
		// Set up photos with different ratings
		$this->photo1->rating_avg = '5.0000';
		$this->photo1->save();

		$this->photo1b->rating_avg = '3.0000';
		$this->photo1b->save();

		$this->subPhoto1->rating_avg = null; // Unrated
		$this->subPhoto1->save();

		// Create query with sorting
		$query = Photo::query()->whereIn('id', [
			$this->photo1->id,
			$this->photo1b->id,
			$this->subPhoto1->id,
		]);

		$results = (new SortingDecorator($query))
			->orderPhotosBy(ColumnSortingType::RATING_AVG, OrderSortingType::ASC)
			->get();

		// Verify order: NULL first (COALESCE -1 < 3.0), then 3.0, then 5.0
		// Note: With COALESCE(rating_avg, -1) ASC, -1 is lowest so NULLs come first
		$this->assertCount(3, $results);
		$this->assertEquals($this->subPhoto1->id, $results[0]->id); // NULL (=-1)
		$this->assertEquals($this->photo1b->id, $results[1]->id); // 3.0
		$this->assertEquals($this->photo1->id, $results[2]->id); // 5.0
	}

	/**
	 * Test sorting with all rated photos (no NULLs).
	 */
	public function testSortingByRatingAllRated(): void
	{
		// Set up photos with different ratings
		$this->photo1->rating_avg = '4.5000';
		$this->photo1->save();

		$this->photo1b->rating_avg = '4.5000'; // Same rating (tie)
		$this->photo1b->save();

		$this->subPhoto1->rating_avg = '2.0000';
		$this->subPhoto1->save();

		// Create query with sorting
		$query = Photo::query()->whereIn('id', [
			$this->photo1->id,
			$this->photo1b->id,
			$this->subPhoto1->id,
		]);

		$results = (new SortingDecorator($query))
			->orderPhotosBy(ColumnSortingType::RATING_AVG, OrderSortingType::DESC)
			->get();

		// Verify order: 4.5 (two photos, order may vary), then 2.0
		$this->assertCount(3, $results);
		$this->assertEquals('4.5000', $results[0]->rating_avg);
		$this->assertEquals('4.5000', $results[1]->rating_avg);
		$this->assertEquals('2.0000', $results[2]->rating_avg);
	}
}
