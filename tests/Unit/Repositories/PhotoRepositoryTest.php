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

namespace Tests\Unit\Repositories;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\PhotoRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

/**
 * Unit tests for PhotoRepository pagination methods.
 */
class PhotoRepositoryTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected User $user;
	protected Album $album;
	protected PhotoRepository $repository;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->may_upload()->create();
		$this->album = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->repository = resolve(PhotoRepository::class);
	}

	private function sorting(): PhotoSortingCriterion
	{
		return new PhotoSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);
	}

	public function testGetPhotosForAlbumPaginatedReturnsLengthAwarePaginator(): void
	{
		Photo::factory()->count(3)->owned_by($this->user)->in($this->album)->create();

		$this->actingAs($this->user);
		$result = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $result);
		$this->assertEquals(3, $result->total());
	}

	public function testGetPhotosForAlbumPaginatedCacheHitDoesNotReexecutePhotoQuery(): void
	{
		Configs::set('managed_cache_enabled', '1');
		Photo::factory()->count(3)->owned_by($this->user)->in($this->album)->create();
		$this->actingAs($this->user);

		$first = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);
		self::assertEquals(3, $first->total());

		// See AlbumRepositoryTest::testGetChildrenPaginatedCacheHitPerformsNoExtraQueries for why
		// this filters to `photos` queries specifically rather than asserting a literal zero:
		// every Cache::get()/put() call fires Illuminate\Cache\Events\*, handled by the
		// pre-existing CacheListener, which does its own unrelated `configs` table read.
		DB::enableQueryLog();
		$second = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);
		$photo_queries = array_filter(DB::getQueryLog(), fn ($q) => preg_match('/from\s*[`"]?photos[`"]?/i', $q['query']) === 1);
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertCount(0, $photo_queries, 'A managed-cache hit must not re-execute the photo query.');
		self::assertEquals(3, $second->total());
	}

	public function testGetPhotosForAlbumPaginatedIgnoresCacheWhenManagedCacheDisabled(): void
	{
		Configs::set('managed_cache_enabled', '0');
		Photo::factory()->count(2)->owned_by($this->user)->in($this->album)->create();
		$this->actingAs($this->user);

		$this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);

		DB::enableQueryLog();
		$this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);
		$photo_queries = array_filter(DB::getQueryLog(), fn ($q) => preg_match('/from\s*[`"]?photos[`"]?/i', $q['query']) === 1);
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertGreaterThan(0, count($photo_queries), 'With managed_cache_enabled=false every call must recompute.');
	}

	/**
	 * NFR-052-05: the cached (then retrieved) paginator's items, pagination
	 * metadata, and eager-loaded relations must match a freshly-queried one.
	 */
	public function testGetPhotosForAlbumPaginatedRoundTripsThroughCacheWithoutLoss(): void
	{
		Configs::set('managed_cache_enabled', '0');
		Photo::factory()->count(3)->owned_by($this->user)->in($this->album)->create();
		$this->actingAs($this->user);

		$fresh = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);

		Configs::set('managed_cache_enabled', '1');
		$cached_write = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);
		$cached_read = $this->repository->getPhotosForAlbumPaginated($this->album->id, $this->sorting(), 10);

		self::assertEquals($fresh->currentPage(), $cached_read->currentPage());
		self::assertEquals($fresh->perPage(), $cached_read->perPage());
		self::assertEquals($fresh->total(), $cached_read->total());
		self::assertEquals(
			array_map(fn (Photo $p) => $p->id, $fresh->items()),
			array_map(fn (Photo $p) => $p->id, $cached_read->items()),
		);
		self::assertEquals($cached_write->items()[0]->id, $cached_read->items()[0]->id);

		foreach ($cached_read->items() as $photo) {
			self::assertTrue($photo->relationLoaded('size_variants'));
			self::assertTrue($photo->relationLoaded('tags'));
			self::assertTrue($photo->relationLoaded('statistics'));
		}
	}
}
