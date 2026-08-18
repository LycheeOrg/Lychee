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

use App\Actions\Album\CreatePersonAlbum;
use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Events\PersonAlbumSaved;
use App\Events\TagAlbumSaved;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use App\Repositories\AlbumRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyTags;
use Tests\Traits\RequiresEmptyUsers;

/**
 * Unit tests for AlbumRepository pagination methods.
 */
class AlbumRepositoryTest extends AbstractTestCase
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use RequiresEmptyTags;

	protected User $user;
	protected Album $parentAlbum;
	protected AlbumRepository $repository;

	public function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyTags();

		$this->user = User::factory()->may_upload()->create();
		$this->parentAlbum = Album::factory()->as_root()->owned_by($this->user)->create();
		$this->repository = resolve(AlbumRepository::class);
	}

	public function tearDown(): void
	{
		// This test class does not run inside a DB transaction (see
		// RequiresEmptyAlbums/RequiresEmptyUsers), so any config toggled by
		// an individual test must be restored here to avoid leaking state
		// into the shared test database for other test classes.
		Configs::set('managed_cache_albums_enabled', '1');
		Configs::set('managed_cache_enabled', '1');

		// Faces/persons are not covered by RequiresEmptyPhotos and must be
		// cleaned up before albums (photo_album/faces reference photos/albums).
		// tag_albums_tags/albums_tags cascade-delete when tag_albums/albums/tags
		// are removed, so explicit cleanup order relative to RequiresEmptyTags
		// does not matter.
		DB::table('faces')->delete();
		DB::table('person_albums_persons')->delete();
		DB::table('persons')->delete();

		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyTags();
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

	// ── Managed cache adoption (Feature 053) ─────────────────────

	private function countAlbumTableQueries(\Closure $callback): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$callback();
		$count = count(array_filter(
			DB::getQueryLog(),
			// Identifier quoting is driver-specific (SQLite/Postgres use
			// "double quotes", MySQL/MariaDB use `backticks`) — strip both
			// before matching so this works under any test DB driver. Match
			// "albums" as a whole word: some matching-album queries also hit
			// person_albums_persons, whose name contains "albums" as a
			// substring but must not be mistaken for a hit on the albums table.
			fn (array $q) => preg_match('/\balbums\b/', str_replace(['"', '`'], '', $q['query'])) === 1
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		return $count;
	}

	public function testCacheHitPerformsNoAlbumsTableQueries(): void
	{
		Album::factory()->count(3)->children_of($this->parentAlbum)->owned_by($this->user)->create();
		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10)
		);

		$this->assertSame(0, $second_call_count, 'A cache hit must not run any query against albums/base_albums.');
	}

	public function testManagedCacheAlbumsDisabledAlwaysRunsLiveQuery(): void
	{
		Configs::set('managed_cache_albums_enabled', '0');
		Album::factory()->count(2)->children_of($this->parentAlbum)->owned_by($this->user)->create();
		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10)
		);

		$this->assertGreaterThan(0, $second_call_count, 'managed_cache_albums_enabled=false must disable caching for this consumer.');
	}

	public function testManagedCacheDisabledAlwaysRunsLiveQuery(): void
	{
		Configs::set('managed_cache_enabled', '0');
		Album::factory()->count(2)->children_of($this->parentAlbum)->owned_by($this->user)->create();
		$sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::DESC);

		$this->actingAs($this->user);
		$this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getChildrenPaginated($this->parentAlbum->id, $sorting, 10)
		);

		$this->assertGreaterThan(0, $second_call_count, 'The master managed_cache_enabled switch must win regardless of the per-part toggle.');
	}

	// ── getMatchingAlbumsForTagPaginated (TagAlbum) ──────────────

	public function testGetMatchingAlbumsForTagPaginatedReturnsTaggedAlbum(): void
	{
		$tag = Tag::factory()->create();
		$tagAlbum = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$this->parentAlbum->tags()->sync([$tag->id]);

		$this->actingAs($this->user);
		$result = $this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $result);
		$this->assertEquals(1, $result->total());
		$this->assertEquals($this->parentAlbum->id, $result->items()[0]->id);
	}

	public function testGetMatchingAlbumsForTagPaginatedEmptyWhenUntagged(): void
	{
		$tag = Tag::factory()->create();
		$tagAlbum = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();

		$this->actingAs($this->user);
		$result = $this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10);

		$this->assertEquals(0, $result->total());
	}

	public function testGetMatchingAlbumsForTagPaginatedCacheHitPerformsNoAlbumsTableQueries(): void
	{
		$tag = Tag::factory()->create();
		$tagAlbum = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$this->parentAlbum->tags()->sync([$tag->id]);

		$this->actingAs($this->user);
		$this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10)
		);

		$this->assertSame(0, $second_call_count, 'A cache hit must not run any query against albums/base_albums.');
	}

	/**
	 * Regression test: a cached matching-albums page for a TagAlbum must
	 * carry that TagAlbum's own albumTag() so that
	 * ManagedCacheAlbumListingInvalidator::handleTagAlbumSaved() (fired when
	 * the smart album's criteria are edited) can evict it. Before the fix,
	 * the cached entry was only tagged with per-tag-id and user tags, so a
	 * criteria edit left the stale page cached.
	 */
	public function testGetMatchingAlbumsForTagPaginatedInvalidatedByTagAlbumSaved(): void
	{
		$tag = Tag::factory()->create();
		$tagAlbum = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$this->parentAlbum->tags()->sync([$tag->id]);

		$this->actingAs($this->user);
		$this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10);

		TagAlbumSaved::dispatch([$tagAlbum->id]);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getMatchingAlbumsForTagPaginated($tagAlbum, 10)
		);

		$this->assertGreaterThan(0, $second_call_count, 'A TagAlbumSaved event must evict this TagAlbum\'s cached matching-albums page.');
	}

	// ── getMatchingAlbumsForPersonPaginated (PersonAlbum) ────────

	public function testGetMatchingAlbumsForPersonPaginatedReturnsContainingAlbum(): void
	{
		$person = Person::factory()->create(['is_searchable' => true]);
		$photo = Photo::factory()->owned_by($this->user)->in($this->parentAlbum)->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();

		$this->actingAs($this->user);
		$personAlbum = resolve(CreatePersonAlbum::class)->create('person_album', [$person->id], false);

		$result = $this->repository->getMatchingAlbumsForPersonPaginated($personAlbum, 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $result);
		$this->assertEquals(1, $result->total());
		$this->assertEquals($this->parentAlbum->id, $result->items()[0]->id);
	}

	public function testGetMatchingAlbumsForPersonPaginatedEmptyWhenNoMatchingPhoto(): void
	{
		$person = Person::factory()->create(['is_searchable' => true]);

		$this->actingAs($this->user);
		$personAlbum = resolve(CreatePersonAlbum::class)->create('person_album', [$person->id], false);

		$result = $this->repository->getMatchingAlbumsForPersonPaginated($personAlbum, 10);

		$this->assertEquals(0, $result->total());
	}

	/**
	 * Regression test: a cached matching-albums page for a PersonAlbum must
	 * carry that PersonAlbum's own albumTag() so that
	 * ManagedCacheAlbumListingInvalidator::handlePersonAlbumSaved() (fired
	 * when the smart album's criteria are edited) can evict it. Before the
	 * fix, the cached entry was only tagged with per-person-id and user
	 * tags, so a criteria edit left the stale page cached.
	 */
	public function testGetMatchingAlbumsForPersonPaginatedInvalidatedByPersonAlbumSaved(): void
	{
		$person = Person::factory()->create(['is_searchable' => true]);
		$photo = Photo::factory()->owned_by($this->user)->in($this->parentAlbum)->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();

		$this->actingAs($this->user);
		$personAlbum = resolve(CreatePersonAlbum::class)->create('person_album', [$person->id], false);
		$this->repository->getMatchingAlbumsForPersonPaginated($personAlbum, 10);

		PersonAlbumSaved::dispatch($personAlbum);

		$second_call_count = $this->countAlbumTableQueries(
			fn () => $this->repository->getMatchingAlbumsForPersonPaginated($personAlbum, 10)
		);

		$this->assertGreaterThan(0, $second_call_count, 'A PersonAlbumSaved event must evict this PersonAlbum\'s cached matching-albums page.');
	}
}
