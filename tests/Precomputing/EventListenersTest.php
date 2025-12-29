<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Precomputing;

use App\Events\AlbumDeleted;
use App\Events\AlbumSaved;
use App\Events\PhotoDeleted;
use App\Events\PhotoSaved;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Listeners\RecomputeAlbumStatsOnAlbumChange;
use App\Listeners\RecomputeAlbumStatsOnPhotoChange;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test event listeners for album stats recomputation.
 */
class EventListenersTest extends BasePrecomputingTest
{
	/**
	 * Test PhotoSaved event dispatches job for photo's albums.
	 *
	 * @return void
	 */
	public function testPhotoSavedDispatchesJobs(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album1 = Album::factory()->as_root()->owned_by($user)->create();
		$album2 = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach([$album1->id, $album2->id]);

		// Dispatch PhotoSaved event
		$event = new PhotoSaved($photo);
		$listener = new RecomputeAlbumStatsOnPhotoChange();
		$listener->handlePhotoSaved($event);

		// Assert jobs dispatched for both albums
		Queue::assertPushed(RecomputeAlbumStatsJob::class, 2);
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album1) {
			return $job->album_id === $album1->id;
		});
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album2) {
			return $job->album_id === $album2->id;
		});
	}

	/**
	 * Test PhotoSaved event does not dispatch when photo has no albums.
	 *
	 * @return void
	 */
	public function testPhotoSavedSkipsWhenNoAlbums(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		// Photo not attached to any album

		// Dispatch PhotoSaved event
		$event = new PhotoSaved($photo);
		$listener = new RecomputeAlbumStatsOnPhotoChange();
		$listener->handlePhotoSaved($event);

		// Assert no jobs dispatched
		Queue::assertNothingPushed();
	}

	/**
	 * Test PhotoDeleted event dispatches job for album.
	 *
	 * @return void
	 */
	public function testPhotoDeletedDispatchesJob(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Dispatch PhotoDeleted event
		$event = new PhotoDeleted($album->id);
		$listener = new RecomputeAlbumStatsOnPhotoChange();
		$listener->handlePhotoDeleted($event);

		// Assert job dispatched
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test AlbumSaved event dispatches job for album.
	 *
	 * @return void
	 */
	public function testAlbumSavedDispatchesJob(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Dispatch AlbumSaved event
		$event = new AlbumSaved($album);
		$listener = new RecomputeAlbumStatsOnAlbumChange();
		$listener->handleAlbumSaved($event);

		// Assert job dispatched
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test AlbumDeleted event dispatches job for parent.
	 *
	 * @return void
	 */
	public function testAlbumDeletedDispatchesJobForParent(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		// Dispatch AlbumDeleted event with parent_id
		$event = new AlbumDeleted($parent->id);
		$listener = new RecomputeAlbumStatsOnAlbumChange();
		$listener->handleAlbumDeleted($event);

		// Assert job dispatched for parent
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($parent) {
			return $job->album_id === $parent->id;
		});
	}

	/**
	 * Test AlbumDeleted event does not dispatch when no parent.
	 *
	 * @return void
	 */
	public function testAlbumDeletedSkipsWhenNoParent(): void
	{
		Queue::fake();

		// Dispatch AlbumDeleted event with null parent_id
		$event = new AlbumDeleted(null);
		$listener = new RecomputeAlbumStatsOnAlbumChange();
		$listener->handleAlbumDeleted($event);

		// Assert no jobs dispatched
		Queue::assertNothingPushed();
	}
}
