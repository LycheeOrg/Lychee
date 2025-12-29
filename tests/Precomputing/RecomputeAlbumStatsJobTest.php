<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Precomputing;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test RecomputeAlbumStatsJob computation logic.
 */
class RecomputeAlbumStatsJobTest extends BasePrecomputingTest
{
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
			'taken_at' => '2023-01-15 10:00:00',
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'taken_at' => '2023-06-20 14:30:00',
		]);
		$photo3 = Photo::factory()->owned_by($user)->create([
			'taken_at' => '2023-03-10 08:15:00',
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
		$this->assertNull($album->auto_cover_id_max_privilege);
		$this->assertNull($album->auto_cover_id_least_privilege);
	}

	/**
	 * Test job computes max-privilege cover (includes all photos).
	 *
	 * @return void
	 */
	public function testComputesMaxPrivilegeCover(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->owned_by($user)->create([
			'is_public' => true,
			'is_starred' => false,
		]);
		$privatePhoto = Photo::factory()->owned_by($user)->create([
			'is_public' => false,
			'is_starred' => true, // Starred photo should be preferred
		]);

		$publicPhoto->albums()->attach($album->id);
		$privatePhoto->albums()->attach($album->id);

		// Run job
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert max-privilege cover is the starred private photo
		$album->refresh();
		$this->assertEquals($privatePhoto->id, $album->auto_cover_id_max_privilege);
	}

	/**
	 * Test job computes least-privilege cover (respects visibility).
	 *
	 * @return void
	 */
	public function testComputesLeastPrivilegeCover(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->owned_by($user)->create([
			'is_public' => true,
			'is_starred' => false,
		]);
		$privatePhoto = Photo::factory()->owned_by($user)->create([
			'is_public' => false,
			'is_starred' => true,
		]);

		$publicPhoto->albums()->attach($album->id);
		$privatePhoto->albums()->attach($album->id);

		// Run job
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		// Assert least-privilege cover is the public photo (private excluded)
		$album->refresh();
		$this->assertEquals($publicPhoto->id, $album->auto_cover_id_least_privilege);
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
		$photo1 = Photo::factory()->owned_by($user)->create(['taken_at' => '2023-01-01']);
		$photo2 = Photo::factory()->owned_by($user)->create(['taken_at' => '2023-12-31']);
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
		$this->assertEquals(2, $parent->num_photos); // Includes photos from children
		$this->assertNotNull($parent->min_taken_at);
		$this->assertNotNull($parent->max_taken_at);
	}
}
