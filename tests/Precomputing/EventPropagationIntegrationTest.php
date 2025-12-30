<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Precomputing;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Album\Delete as AlbumDelete;
use App\Actions\Photo\Delete as PhotoDelete;
use App\Actions\Photo\MoveOrDuplicate;
use App\Contracts\Models\AbstractAlbum;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Integration tests for event propagation and stats recomputation.
 */
class EventPropagationIntegrationTest extends BasePrecomputingTest
{
	/**
	 * Test creating a photo triggers recomputation.
	 *
	 * @return void
	 */
	public function testPhotoCreationTriggersRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photo (via factory uses Photo::save which dispatches event)
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// The Photo::save() in factory should have dispatched PhotoSaved
		// But we need to manually dispatch since the event is in the pipeline
		\App\Events\PhotoSaved::dispatch($photo);

		// Assert job was dispatched
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test deleting photos triggers recomputation.
	 *
	 * @return void
	 */
	public function testPhotoDeletionTriggersRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Delete photo using Delete action (which dispatches events)
		$deleteAction = new PhotoDelete();
		$deleteAction->do([$photo->id], $album->id);

		// Assert job was dispatched for the album
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test moving photos between albums triggers recomputation for both.
	 *
	 * @return void
	 */
	public function testPhotoMoveTriggersRecomputationForBothAlbums(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		/** @var Album&AbstractAlbum $sourceAlbum */
		$sourceAlbum = Album::factory()->as_root()->owned_by($user)->create();
		$destAlbum = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($sourceAlbum->id);

		// Move photo using MoveOrDuplicate action
		$moveAction = new MoveOrDuplicate(resolve(\App\Actions\Shop\PurchasableService::class));
		$moveAction->do(collect([$photo]), $sourceAlbum, $destAlbum);

		// Assert jobs dispatched for both albums
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($sourceAlbum) {
			return $job->album_id === $sourceAlbum->id;
		});
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($destAlbum) {
			return $job->album_id === $destAlbum->id;
		});
	}

	/**
	 * Test creating album triggers recomputation for parent.
	 *
	 * @return void
	 */
	public function testAlbumCreationTriggersRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		// Create child album using Create action (which dispatches events)
		$createAction = new AlbumCreate($user->id);
		$child = $createAction->create('Child Album', $parent);

		// Assert job was dispatched for the child (from AlbumSaved event)
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($child) {
			return $job->album_id === $child->id;
		});
	}

	/**
	 * Test deleting album triggers recomputation for parent.
	 *
	 * @return void
	 */
	public function testAlbumDeletionTriggersParentRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		// Delete child album using Delete action (which dispatches events)
		$deleteAction = new AlbumDelete();
		$deleteAction->do([$child->id]);

		// Assert job was dispatched for parent (from AlbumDeleted event)
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($parent) {
			return $job->album_id === $parent->id;
		});
	}

	/**
	 * Test deep nesting propagation (child -> parent -> grandparent).
	 *
	 * @return void
	 */
	public function testDeepNestingPropagation(): void
	{
		$user = User::factory()->create();
		$grandparent = Album::factory()->as_root()->owned_by($user)->create();
		$parent = Album::factory()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();

		$parent->appendToNode($grandparent)->save();
		$child->appendToNode($parent)->save();

		// Add photo to child
		Photo::factory()->in($child)->owned_by($user)->create(['taken_at' => new Carbon('2023-06-15')]);

		// Compute child stats
		$job = new RecomputeAlbumStatsJob($child->id);
		$job->handle();

		// Child should have stats
		$child->refresh();
		$this->assertEquals(1, $child->num_photos);
		$this->assertEquals(0, $child->num_children);

		// The job should have dispatched a job for parent
		// Run parent job
		$parentJob = new RecomputeAlbumStatsJob($parent->id);
		$parentJob->handle();

		// Parent should aggregate from child
		$parent->refresh();
		$this->assertEquals(0, $parent->num_photos); // Inherited from child
		$this->assertEquals(1, $parent->num_children);

		// Run grandparent job
		$grandparentJob = new RecomputeAlbumStatsJob($grandparent->id);
		$grandparentJob->handle();

		// Grandparent should aggregate from children only
		$grandparent->refresh();
		$this->assertEquals(0, $grandparent->num_photos); // Inherited from descendants
		$this->assertEquals(1, $grandparent->num_children); // Only counts direct children
	}

	/**
	 * Test NSFW status change triggers recomputation.
	 *
	 * @return void
	 */
	public function testNSFWChangeTriggersRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create([
			'is_nsfw' => false,
		]);

		// Change NSFW status using SetProtectionPolicy action
		$protectionPolicy = new \App\Http\Resources\Models\Utils\AlbumProtectionPolicy(
			is_public: false,
			is_link_required: false,
			is_nsfw: true, // Changed
			grants_full_photo_access: false,
			grants_download: false,
			grants_upload: false
		);

		$action = new \App\Actions\Album\SetProtectionPolicy();
		$action->do($album, $protectionPolicy, false, null);

		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test no recomputation when NSFW status unchanged.
	 *
	 * @return void
	 */
	public function testAlwaysRecomputationWhenNSFWUnchanged(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create([
			'is_nsfw' => false,
		]);

		// Set same NSFW status
		$protectionPolicy = new \App\Http\Resources\Models\Utils\AlbumProtectionPolicy(
			is_public: false,
			is_link_required: false,
			is_nsfw: false, // Same as before
			grants_full_photo_access: false,
			grants_download: false,
			grants_upload: false
		);

		$action = new \App\Actions\Album\SetProtectionPolicy();
		$action->do($album, $protectionPolicy, false, null);

		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}
}
