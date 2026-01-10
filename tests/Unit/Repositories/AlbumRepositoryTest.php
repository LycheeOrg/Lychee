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

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use App\Repositories\AlbumRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

/**
 * Unit tests for AlbumRepository pagination methods.
 */
class AlbumRepositoryTest extends AbstractTestCase
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;

	protected User $user;
	protected Album $parentAlbum;
	protected AlbumRepository $repository;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();

		$this->user = User::factory()->may_upload()->create();
		$this->parentAlbum = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->repository = resolve(AlbumRepository::class);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();

		parent::tearDown();
	}

	public function testGetChildrenPaginatedReturnsLengthAwarePaginator(): void
	{
		// Create some child albums
		Album::factory()->count(5)->children_of($this->parentAlbum)->owned_by($this->user)->create();

		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $result);
		$this->assertEquals(5, $result->total());
		$this->assertEquals(1, $result->currentPage());
		$this->assertEquals(10, $result->perPage());
		$this->assertEquals(1, $result->lastPage());
	}

	public function testGetChildrenPaginatedWithPagination(): void
	{
		// Create 15 child albums to test pagination
		Album::factory()->count(15)->children_of($this->parentAlbum)->owned_by($this->user)->create();

		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);

		// Get first page with 5 per page
		$page1 = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 5);

		$this->assertEquals(15, $page1->total());
		$this->assertEquals(1, $page1->currentPage());
		$this->assertEquals(5, $page1->perPage());
		$this->assertEquals(3, $page1->lastPage());
		$this->assertCount(5, $page1->items());
	}

	public function testGetChildrenPaginatedEmptyAlbum(): void
	{
		// Parent album has no children
		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $result);
		$this->assertEquals(0, $result->total());
		$this->assertEquals(1, $result->currentPage());
		$this->assertCount(0, $result->items());
	}

	public function testGetChildrenPaginatedBeyondAvailablePages(): void
	{
		// Create 3 child albums
		Album::factory()->count(3)->children_of($this->parentAlbum)->owned_by($this->user)->create();

		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);

		// Request page 5 with 10 per page (only 1 page exists)
		request()->merge(['page' => 5]);
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$this->assertEquals(3, $result->total());
		$this->assertEquals(5, $result->currentPage());
		$this->assertEquals(1, $result->lastPage());
		$this->assertCount(0, $result->items());
	}

	public function testGetChildrenPaginatedSorting(): void
	{
		// Create albums with specific titles for sorting verification
		$albumA = Album::factory()->children_of($this->parentAlbum)->owned_by($this->user)->create(['title' => 'A Album']);
		$albumZ = Album::factory()->children_of($this->parentAlbum)->owned_by($this->user)->create(['title' => 'Z Album']);
		$albumM = Album::factory()->children_of($this->parentAlbum)->owned_by($this->user)->create(['title' => 'M Album']);

		$this->actingAs($this->user);

		// Sort by title ascending
		$sortingAsc = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$resultAsc = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sortingAsc, 10);
		$itemsAsc = $resultAsc->items();
		$this->assertEquals('A Album', $itemsAsc[0]->title);
		$this->assertEquals('M Album', $itemsAsc[1]->title);
		$this->assertEquals('Z Album', $itemsAsc[2]->title);

		// Sort by title descending
		$sortingDesc = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::DESC);
		$resultDesc = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sortingDesc, 10);
		$itemsDesc = $resultDesc->items();
		$this->assertEquals('Z Album', $itemsDesc[0]->title);
		$this->assertEquals('M Album', $itemsDesc[1]->title);
		$this->assertEquals('A Album', $itemsDesc[2]->title);
	}

	public function testGetChildrenPaginatedRespectsVisibility(): void
	{
		// Create another user
		$otherUser = User::factory()->may_upload()->create();
		$otherAlbum = Album::factory()->as_root()->owned_by($otherUser)->create();

		// Create child albums owned by different users
		Album::factory()->count(3)->children_of($this->parentAlbum)->owned_by($this->user)->create();

		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		// As the owner, should see all children
		$this->actingAs($this->user);
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);
		$this->assertEquals(3, $result->total());

		// As anonymous user, should not see private albums
		$this->app['auth']->forgetGuards();
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);
		$this->assertEquals(0, $result->total());
	}

	public function testGetChildrenPaginatedWithPublicAlbums(): void
	{
		// Create child albums and make some public
		$publicAlbum = Album::factory()->children_of($this->parentAlbum)->owned_by($this->user)->create();
		Album::factory()->count(2)->children_of($this->parentAlbum)->owned_by($this->user)->create();

		// Make one album public
		AccessPermission::factory()->public()->visible()->for_album($publicAlbum)->create();

		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		// As anonymous user, should only see the public album
		$this->app['auth']->forgetGuards();
		$result = $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);
		$this->assertEquals(1, $result->total());
		$this->assertEquals($publicAlbum->id, $result->items()[0]->id);
	}

	public function testGetChildrenPaginatedForRootAlbums(): void
	{
		// Test getting root albums (parent_id = null)
		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$result = $this->repository->getChildrenPaginated(null, $sorting, 10);

		// Should include the parentAlbum (which is a root album)
		$this->assertGreaterThanOrEqual(1, $result->total());
		$albumIds = array_map(fn ($item) => $item->id, $result->items());
		$this->assertContains($this->parentAlbum->id, $albumIds);
	}
}
