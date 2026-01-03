<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\SizeComputations;

use App\Actions\Photo\Delete as PhotoDelete;
use App\Actions\Photo\MoveOrDuplicate;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\Album;
use App\Models\AlbumSizeStatistics;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Integration tests for event-driven album size statistics recomputation (T-004-21).
 *
 * Verifies that photo and size variant mutations trigger recomputation jobs.
 */
class AlbumSizeEventListenerTest extends BasePrecomputingTest
{
	/**
	 * Test photo creation triggers size recomputation.
	 *
	 * @return void
	 */
	public function testPhotoCreationTriggersRecomputation(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photo (factory creates with size variants)
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Dispatch PhotoSaved event
		\App\Events\PhotoSaved::dispatch($photo);

		// Assert RecomputeAlbumSizeJob was dispatched for the album
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test photo deletion triggers size recomputation.
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

		// Delete photo using Delete action (dispatches events)
		$deleteAction = new PhotoDelete();
		$deleteAction->do([$photo->id], $album->id);

		// Assert job was dispatched for the album
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test moving photo between albums triggers recomputation for both.
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
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($sourceAlbum) {
			return $job->album_id === $sourceAlbum->id;
		});
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($destAlbum) {
			return $job->album_id === $destAlbum->id;
		});
	}

	/**
	 * Test size variant regeneration updates statistics (end-to-end without event).
	 *
	 * Note: SizeVariant events not yet implemented. This tests manual recomputation.
	 *
	 * @return void
	 */
	public function testVariantRegenerationManualRecomputation(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Initial computation
		(new RecomputeAlbumSizeJob($album->id))->handle();
		$initialStats = AlbumSizeStatistics::find($album->id);
		$initialSize = $initialStats->size_medium;

		// Regenerate a variant (simulate by updating filesize)
		$variant = SizeVariant::where('photo_id', $photo->id)
			->where('type', SizeVariantType::MEDIUM)
			->first();

		$variant->filesize = 999999;
		$variant->save();

		// Manually trigger recomputation (event listeners would do this automatically)
		(new RecomputeAlbumSizeJob($album->id))->handle();

		// Verify statistics updated
		$updatedStats = AlbumSizeStatistics::find($album->id);
		$this->assertEquals(999999, $updatedStats->size_medium);
		$this->assertNotEquals($initialSize, $updatedStats->size_medium);
	}

	/**
	 * Test size variant deletion updates statistics (end-to-end without event).
	 *
	 * Note: SizeVariant events not yet implemented. This tests manual recomputation.
	 *
	 * @return void
	 */
	public function testVariantDeletionManualRecomputation(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Initial computation
		(new RecomputeAlbumSizeJob($album->id))->handle();
		$initialStats = AlbumSizeStatistics::find($album->id);
		$this->assertGreaterThan(0, $initialStats->size_small);

		$variant = SizeVariant::where('photo_id', $photo->id)
			->where('type', SizeVariantType::SMALL)
			->first();

		// Delete variant
		$variant->delete();

		// Manually trigger recomputation
		(new RecomputeAlbumSizeJob($album->id))->handle();

		// Verify statistics updated (SMALL size should now be 0)
		$updatedStats = AlbumSizeStatistics::find($album->id);
		$this->assertEquals(0, $updatedStats->size_small);
	}

	/**
	 * Test photo in multiple albums triggers recomputation for all.
	 *
	 * @return void
	 */
	public function testPhotoInMultipleAlbumsTriggersAllRecomputations(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album1 = Album::factory()->as_root()->owned_by($user)->create();
		$album2 = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach([$album1->id, $album2->id]);

		// Update photo (triggers PhotoSaved)
		\App\Events\PhotoSaved::dispatch($photo);

		// Assert jobs dispatched for both albums
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($album1) {
			return $job->album_id === $album1->id;
		});
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($album2) {
			return $job->album_id === $album2->id;
		});
	}

	/**
	 * Test end-to-end: photo upload, variant generation, and statistics update.
	 *
	 * @return void
	 */
	public function testEndToEndPhotoUploadUpdatesStatistics(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Initially, album has no statistics
		$initialStats = AlbumSizeStatistics::find($album->id);
		$this->assertNull($initialStats);

		// Create photo with variants
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Run recomputation job
		$job = new RecomputeAlbumSizeJob($album->id);
		$job->handle();

		// Verify statistics created
		$stats = AlbumSizeStatistics::find($album->id);
		$this->assertNotNull($stats);
		$this->assertGreaterThan(0, $stats->size_original);
		$this->assertGreaterThan(0, $stats->size_thumb);
	}

	/**
	 * Test end-to-end: variant regeneration updates statistics.
	 *
	 * @return void
	 */
	public function testEndToEndVariantRegenerationUpdatesStatistics(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Initial computation
		(new RecomputeAlbumSizeJob($album->id))->handle();
		$initialStats = AlbumSizeStatistics::find($album->id);
		$initialSize = $initialStats->size_medium;

		// Regenerate variant with different size
		$variant = SizeVariant::where('photo_id', $photo->id)
			->where('type', SizeVariantType::MEDIUM)
			->first();
		$variant->filesize = 123456;
		$variant->save();

		// Re-run computation
		(new RecomputeAlbumSizeJob($album->id))->handle();

		// Verify statistics updated
		$updatedStats = AlbumSizeStatistics::find($album->id);
		$this->assertEquals(123456, $updatedStats->size_medium);
		$this->assertNotEquals($initialSize, $updatedStats->size_medium);
	}

	/**
	 * Test end-to-end: photo deletion clears statistics.
	 *
	 * @return void
	 */
	public function testEndToEndPhotoDeletionUpdatesStatistics(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Initial computation
		(new RecomputeAlbumSizeJob($album->id))->handle();
		$initialStats = AlbumSizeStatistics::find($album->id);
		$this->assertGreaterThan(0, $initialStats->size_original);

		// Delete photo
		$deleteAction = new PhotoDelete();
		$deleteAction->do([$photo->id], $album->id);

		// Re-run computation
		(new RecomputeAlbumSizeJob($album->id))->handle();

		// Verify statistics show zero (album now empty)
		$updatedStats = AlbumSizeStatistics::find($album->id);
		$this->assertNotNull($updatedStats);
		$this->assertEquals(0, $updatedStats->size_original);
		$this->assertEquals(0, $updatedStats->size_thumb);
	}
}
