<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Events\AlbumComputedDataUpdated;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Listeners\RecomputeAlbumSizeOnAlbumChange;
use App\Listeners\RecomputeAlbumStatsOnAlbumChange;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAlbumStatsJob computation logic.
 */
class RecomputeAlbumStatsJobTest extends BasePrecomputingTest
{
	/**
	 * Query count of `RecomputeAlbumStatsJob::handle()` for a 3-photo public
	 * root album: 35 before Feature 075 (NFR-075-02, side covers add none);
	 * Feature 076 loads the album with one query fewer (`autoCoverRows`
	 * instead of the two `*_privilege_cover` relations, NFR-076-02) and adds
	 * the delete and the insert of the precomputed cover rows (NFR-076-03).
	 */
	private const JOB_QUERY_BASELINE = 36;

	public function testHandleDispatchesAlbumComputedDataUpdated(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		Event::fake([AlbumComputedDataUpdated::class]);

		$job = new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false);
		$job->handle();

		Event::assertDispatched(AlbumComputedDataUpdated::class, fn (AlbumComputedDataUpdated $e) => $e->album_id === $album->id);
	}

	/**
	 * Loop-safety proof (NFR-053-01): `AlbumComputedDataUpdated` must never be
	 * listened to by the two `RecomputeAlbum*OnAlbumChange` listeners, or a job
	 * completing would re-trigger itself indefinitely.
	 */
	public function testAlbumComputedDataUpdatedDoesNotReDispatchRecomputeJobs(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		Queue::fake();

		AlbumComputedDataUpdated::dispatch($album->id);

		Queue::assertNothingPushed();
	}

	public function testRecomputeAlbumStatsOnAlbumChangeHasNoAlbumComputedDataUpdatedListenerMethod(): void
	{
		$this->assertFalse(
			method_exists(RecomputeAlbumStatsOnAlbumChange::class, 'handleAlbumComputedDataUpdated'),
			'RecomputeAlbumStatsOnAlbumChange must not react to AlbumComputedDataUpdated (would cause a dispatch loop).'
		);
		$this->assertFalse(
			method_exists(RecomputeAlbumSizeOnAlbumChange::class, 'handleAlbumComputedDataUpdated'),
			'RecomputeAlbumSizeOnAlbumChange must not react to AlbumComputedDataUpdated (would cause a dispatch loop).'
		);
	}

	/**
	 * Test job computes num_photos correctly.
	 *
	 * @return void
	 */
	public function testComputesNumPhotos(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create 3 photos in album
		$photo1 = Photo::factory()->owned_by($user)->create();
		$photo2 = Photo::factory()->owned_by($user)->create();
		$photo3 = Photo::factory()->owned_by($user)->create();

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);
		$photo3->albums()->attach($album->id);

		// Run job
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert num_photos = 3
		$album->refresh();
		$this->assertEquals(3, $album->num_photos);
	}

	/**
	 * Test job computes num_children correctly.
	 *
	 * @return void
	 */
	public function testComputesNumChildren(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		// Create 2 child albums
		$child1 = Album::factory()->owned_by($user)->create();
		$child1->appendToNode($parent)->save();

		$child2 = Album::factory()->owned_by($user)->create();
		$child2->appendToNode($parent)->save();

		// Run job
		$job = new RecomputeAlbumStatsJob($parent->id);
		$job->handle();

		// Assert num_children = 2
		$parent->refresh();
		$this->assertEquals(2, $parent->num_children);
	}

	/**
	 * Test job computes min_taken_at and max_taken_at correctly.
	 *
	 * @return void
	 */
	public function testComputesTakenAtRange(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photos with different taken_at dates
		$photo1 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-01-15 10:00:00', 'UTC'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-20 14:30:00', 'UTC'),
		]);
		$photo3 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-03-10 08:15:00', 'UTC'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);
		$photo3->albums()->attach($album->id);

		// Run job
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert min/max dates
		$album->refresh();
		$this->assertEquals('2023-01-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-20 14:30:00', $album->max_taken_at->format('Y-m-d H:i:s'));
	}

	/**
	 * Test job handles empty album (all fields NULL or 0).
	 *
	 * @return void
	 */
	public function testHandlesEmptyAlbum(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Run job on empty album
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert all computed fields are zero/null
		$album->refresh();
		$this->assertEquals(0, $album->num_photos);
		$this->assertEquals(0, $album->num_children);
		$this->assertNull($album->min_taken_at);
		$this->assertNull($album->max_taken_at);
		$this->assertNull($this->maxCovers($album)[0]);
		$this->assertNull($this->leastCovers($album)[0]);
	}

	/**
	 * Test job computes max-privilege cover (includes all photos).
	 *
	 * @return void
	 */
	public function testComputesMaxLeastPrivilegeCover(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$album2 = Album::factory()->children_of($album)->owned_by($user)->create();
		$album3 = Album::factory()->children_of($album)->owned_by($user)->create();

		AccessPermission::factory()->public()->visible()->for_album($album2)->create();
		AccessPermission::factory()->public()->for_album($album3)->create();
		AccessPermission::factory()->public()->for_album($album)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->in($album2)->owned_by($user)->create([
			'is_highlighted' => false,
		]);
		$privatePhoto = Photo::factory()->in($album3)->owned_by($user)->create([
			'is_highlighted' => true, // Highlighted photo should be preferred
		]);

		// Run job
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert max-privilege cover is the highlighted private photo
		$album->refresh();
		$this->assertEquals($privatePhoto->id, $this->maxCovers($album)[0]);
		$this->assertEquals($publicPhoto->id, $this->leastCovers($album)[0]);
	}

	/**
	 * Test job propagates to parent album.
	 *
	 * @return void
	 */
	public function testPropagesToParent(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		// Run job on child
		$job = new RecomputeAlbumStatsJob($child->id);
		$job->handle();

		// Assert parent job was dispatched
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($parent) {
			return $job->album_id === $parent->id;
		});
	}

	/**
	 * Test job does not propagate when no parent.
	 *
	 * @return void
	 */
	public function testDoesNotPropagateWithoutParent(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$rootAlbum = Album::factory()->as_root()->owned_by($user)->create();

		// Run job on root album
		$job = new RecomputeAlbumStatsJob($rootAlbum->id);
		$job->handle();

		// Assert no additional jobs dispatched (only the original)
		Queue::assertNothingPushed();
	}

	/**
	 * Test job aggregates stats from child albums.
	 *
	 * @return void
	 */
	public function testAggregatesFromChildren(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child1 = Album::factory()->owned_by($user)->create();
		$child2 = Album::factory()->owned_by($user)->create();

		$child1->appendToNode($parent)->save();
		$child2->appendToNode($parent)->save();

		// Add photos to children
		$photo1 = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2023-01-01')]);
		$photo2 = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2023-12-31')]);
		$photo1->albums()->attach($child1->id);
		$photo2->albums()->attach($child2->id);

		// Compute children first
		(new RecomputeAlbumStatsJob($child1->id))->handle();
		(new RecomputeAlbumStatsJob($child2->id))->handle();

		// Then compute parent
		$job = new RecomputeAlbumStatsJob($parent->id);
		$job->handle();

		// Assert parent aggregates from children
		$parent->refresh();
		$this->assertEquals(2, $parent->num_children);
		$this->assertEquals(0, $parent->num_photos); // exclude photos from children
		$this->assertNotNull($parent->min_taken_at);
		$this->assertNotNull($parent->max_taken_at);
	}

	/**
	 * Test job computes nsfw cover visibility.
	 *
	 * @return void
	 */
	public function testComputesMaxLeastNsfwPrivilegeCover(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$album2 = Album::factory()->children_of($album)->owned_by($user)->create();
		$album3 = Album::factory()->children_of($album)->owned_by($user)->create(['is_nsfw' => true]);

		AccessPermission::factory()->public()->visible()->for_album($album2)->create();
		AccessPermission::factory()->public()->visible()->for_album($album3)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->in($album2)->owned_by($user)->create([
			'is_highlighted' => false,
		]);
		$nsfwPhoto = Photo::factory()->in($album3)->owned_by($user)->create([
			'is_highlighted' => true,
		]);

		// Run job
		$job = new RecomputeAlbumStatsJob($album3->id);
		$job->handle();

		// Assert no sensitive picture is accessible.
		$album->refresh();
		$this->assertEquals($publicPhoto->id, $this->maxCovers($album)[0]);
		$this->assertEquals($publicPhoto->id, $this->leastCovers($album)[0]);

		$album3->refresh();
		$this->assertEquals($nsfwPhoto->id, $this->maxCovers($album3)[0]);
		$this->assertEquals($nsfwPhoto->id, $this->leastCovers($album3)[0]);
	}

	/**
	 * Test job computes nsfw cover visibility.
	 *
	 * @return void
	 */
	public function testComputesMaxLeastNsfwDeepNestedPrivilegeCover(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create(['is_nsfw' => true]);
		$album2 = Album::factory()->children_of($album)->owned_by($user)->create();
		$album3 = Album::factory()->children_of($album)->owned_by($user)->create();

		AccessPermission::factory()->public()->visible()->for_album($album2)->create();
		AccessPermission::factory()->public()->visible()->for_album($album3)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->in($album2)->owned_by($user)->create([
			'is_highlighted' => false,
		]);
		$nsfwPhoto = Photo::factory()->in($album3)->owned_by($user)->create([
			'is_highlighted' => true,
		]);

		// Run job
		$job = new RecomputeAlbumStatsJob($album3->id);
		$job->handle();

		// Assert no sensitive picture is accessible.
		$album->refresh();
		$this->assertEquals($nsfwPhoto->id, $this->maxCovers($album)[0]);
		$this->assertEquals($nsfwPhoto->id, $this->leastCovers($album)[0]);

		$album3->refresh();
		$this->assertEquals($nsfwPhoto->id, $this->maxCovers($album3)[0]);
		$this->assertEquals($nsfwPhoto->id, $this->leastCovers($album3)[0]);
	}

	// ── Feature 075: side covers (FR-075-02, S-075-01, S-075-02, NFR-075-02) ──

	private function useCreatedAtAscendingPhotoSorting(): void
	{
		Configs::set('sorting_photos_col', 'created_at');
		Configs::set('sorting_photos_order', 'ASC');
	}

	/**
	 * S-075-01: ranks 1–3 follow the same ordering as the cover: highlighted
	 * first, then the album's effective photo sorting.
	 */
	public function testComputesSideCoversInEffectiveOrder(): void
	{
		$this->useCreatedAtAscendingPhotoSorting();
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		$first = Photo::factory()->in($album)->owned_by($user)->create(['created_at' => Carbon::parse('2024-01-01 10:00:00')]);
		$second = Photo::factory()->in($album)->owned_by($user)->create(['created_at' => Carbon::parse('2024-01-02 10:00:00')]);
		$third = Photo::factory()->in($album)->owned_by($user)->create(['created_at' => Carbon::parse('2024-01-03 10:00:00')]);
		$highlighted = Photo::factory()->in($album)->owned_by($user)->create(['created_at' => Carbon::parse('2024-01-04 10:00:00'), 'is_highlighted' => true]);

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		$album->refresh();

		$this->assertSame($highlighted->id, $this->maxCovers($album)[0]);
		$this->assertSame($first->id, $this->maxCovers($album)[1]);
		$this->assertSame($second->id, $this->maxCovers($album)[2]);
		$this->assertSame($highlighted->id, $this->leastCovers($album)[0]);
		$this->assertSame($first->id, $this->leastCovers($album)[1]);
		$this->assertSame($second->id, $this->leastCovers($album)[2]);
		$this->assertNotSame($third->id, $this->maxCovers($album)[2]);
	}

	/**
	 * S-075-02: fewer than three qualifying photos leave the higher ranks NULL.
	 */
	public function testSideCoversAreNullWhenFewerPhotosQualify(): void
	{
		$this->useCreatedAtAscendingPhotoSorting();
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		$only = Photo::factory()->in($album)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		$album->refresh();

		$this->assertSame($only->id, $this->maxCovers($album)[0]);
		$this->assertNull($this->maxCovers($album)[1]);
		$this->assertNull($this->maxCovers($album)[2]);
		$this->assertSame($only->id, $this->leastCovers($album)[0]);
		$this->assertNull($this->leastCovers($album)[1]);
		$this->assertNull($this->leastCovers($album)[2]);
	}

	/**
	 * S-075-02: an empty album has every cover column NULL.
	 */
	public function testEmptyAlbumHasNullSideCovers(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		$album->refresh();

		$this->assertNull($this->maxCovers($album)[1]);
		$this->assertNull($this->maxCovers($album)[2]);
		$this->assertNull($this->leastCovers($album)[1]);
		$this->assertNull($this->leastCovers($album)[2]);
	}

	/**
	 * NFR-075-02 / NFR-076-03: side covers ride on the cover query
	 * (`limit(3)` instead of `first()`), and the cover rows cost exactly two
	 * writes, see {@see self::JOB_QUERY_BASELINE}.
	 */
	public function testSideCoversAddNoQueryToTheJob(): void
	{
		$this->useCreatedAtAscendingPhotoSorting();
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		Photo::factory()->in($album)->owned_by($user)->count(3)->create();

		$job = new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false);
		DB::flushQueryLog();
		DB::enableQueryLog();
		$job->handle();
		$count = count(DB::getQueryLog());
		DB::disableQueryLog();

		$this->assertSame(self::JOB_QUERY_BASELINE, $count, 'RecomputeAlbumStatsJob issued ' . $count . ' queries');
	}

	/**
	 * The `user_id`s of the album's precomputed cover rows (Feature 076).
	 *
	 * @return array<int,int|null>
	 */
	private function coverRowKeys(Album $album): array
	{
		return AlbumUserThumb::query()->where('album_id', '=', $album->id)->where('is_precomputed', '=', true)
			->pluck('user_id')->all();
	}

	/**
	 * S-076-01: several permissions → an owner row and a `NULL` row.
	 */
	public function testPublicAlbumGetsOwnerAndNullRow(): void
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		AccessPermission::factory()->for_user($other)->for_album($album)->create();
		Photo::factory()->in($album)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		$this->assertEqualsCanonicalizing([null, $user->id], $this->coverRowKeys($album));
	}

	/**
	 * S-076-02: an album shared with one user only → the least-privilege
	 * row is keyed on that user, no `NULL` row.
	 */
	public function testSingleShareKeysLeastRowOnSharedUser(): void
	{
		$user = User::factory()->create();
		$shared = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->for_user($shared)->for_album($album)->create();
		$photo = Photo::factory()->in($album)->owned_by($user)->create();

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		$this->assertEqualsCanonicalizing([$user->id, $shared->id], $this->coverRowKeys($album));
		$this->assertSame($photo->id, $this->leastCovers($album)[0]);
	}

	/**
	 * S-076-03: going from a single share to two permissions replaces the
	 * shared user's row by a `NULL` row on the next run (I1).
	 */
	public function testLeastRowIsReKeyedWhenPermissionsChange(): void
	{
		$user = User::factory()->create();
		$shared = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->for_user($shared)->for_album($album)->create();
		Photo::factory()->in($album)->owned_by($user)->create();
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		AccessPermission::factory()->public()->visible()->for_album($album)->create();
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();

		$this->assertEqualsCanonicalizing([null, $user->id], $this->coverRowKeys($album));
	}

	/**
	 * S-076-04: no permission → owner row only; a single permission for the
	 * owner → owner row only (I2); an empty album → no row.
	 */
	public function testOwnerRowOnlyWithoutOtherViewers(): void
	{
		$user = User::factory()->create();
		$private = Album::factory()->as_root()->owned_by($user)->create();
		Photo::factory()->in($private)->owned_by($user)->create();
		$self_shared = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->for_user($user)->for_album($self_shared)->create();
		Photo::factory()->in($self_shared)->owned_by($user)->create();
		$empty = Album::factory()->as_root()->owned_by($user)->create();

		foreach ([$private, $self_shared, $empty] as $album) {
			(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		}

		$this->assertEqualsCanonicalizing([$user->id], $this->coverRowKeys($private));
		$this->assertEqualsCanonicalizing([$user->id], $this->coverRowKeys($self_shared));
		$this->assertEqualsCanonicalizing([], $this->coverRowKeys($empty));
	}

	/**
	 * S-076-17: deleting the rank-1 photo cascades the row away; the next
	 * recompute writes it back with the remaining photo.
	 */
	public function testDeletedCoverPhotoRowIsRecomputed(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$kept = Photo::factory()->in($album)->owned_by($user)->create();
		$cover = Photo::factory()->in($album)->owned_by($user)->create(['is_highlighted' => true]);
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		$this->assertSame($cover->id, $this->maxCovers($album)[0]);

		DB::table('photo_album')->where('photo_id', '=', $cover->id)->delete();
		DB::table('size_variants')->where('photo_id', '=', $cover->id)->delete();
		DB::table('photos')->where('id', '=', $cover->id)->delete();
		$this->assertEqualsCanonicalizing([], $this->coverRowKeys($album));

		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
		$this->assertSame($kept->id, $this->maxCovers($album)[0]);
	}
}
