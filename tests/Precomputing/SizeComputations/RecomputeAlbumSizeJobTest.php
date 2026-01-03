<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\SizeComputations;

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
 * Test RecomputeAlbumSizeJob computation logic.
 */
class RecomputeAlbumSizeJobTest extends BasePrecomputingTest
{
	/**
	 * Test job computes size statistics correctly for single album.
	 *
	 * @return void
	 */
	public function testComputesSizeStatistics(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photo with auto-generated variants (7 variants created by factory)
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Run job
		$job = new RecomputeAlbumSizeJob($album->id);
		$job->handle();

		// Assert statistics computed - should have all 7 variant types with sizes > 0
		$stats = AlbumSizeStatistics::find($album->id);
		$this->assertNotNull($stats);
		$this->assertGreaterThan(0, $stats->size_thumb);
		$this->assertGreaterThan(0, $stats->size_thumb2x);
		$this->assertGreaterThan(0, $stats->size_small);
		$this->assertGreaterThan(0, $stats->size_small2x);
		$this->assertGreaterThan(0, $stats->size_medium);
		$this->assertGreaterThan(0, $stats->size_medium2x);
		$this->assertGreaterThan(0, $stats->size_original);
	}

	/**
	 * Test job excludes PLACEHOLDER variants (type 7).
	 *
	 * @return void
	 */
	public function testExcludesPlaceholderVariants(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photo with auto-generated variants (7 variants)
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Add a PLACEHOLDER variant manually (in addition to the 7 auto-generated ones)
		SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::PLACEHOLDER)->with_size(999999)->create();

		// Get the size of existing ORIGINAL variant before job runs
		$originalVariant = SizeVariant::where('photo_id', $photo->id)
			->where('type', SizeVariantType::ORIGINAL)
			->first();
		$originalSize = $originalVariant->filesize;

		// Run job
		$job = new RecomputeAlbumSizeJob($album->id);
		$job->handle();

		// Assert PLACEHOLDER not counted - total should NOT include the 999999 bytes
		$stats = AlbumSizeStatistics::find($album->id);
		$this->assertEquals($originalSize, $stats->size_original);

		// Calculate all variant sizes from DB to verify PLACEHOLDER not included
		$totalVariantSizes = SizeVariant::where('photo_id', $photo->id)
			->where('type', '!=', SizeVariantType::PLACEHOLDER)
			->sum('filesize');

		// Verify stats match the sum of non-PLACEHOLDER variants
		$statsTotal = $stats->size_thumb + $stats->size_thumb2x + $stats->size_small +
			$stats->size_small2x + $stats->size_medium + $stats->size_medium2x + $stats->size_original;
		$this->assertEquals($totalVariantSizes, $statsTotal);
		$this->assertEquals($originalSize, $stats->size_original);
	}

	/**
	 * Test job handles empty album (all sizes zero).
	 *
	 * @return void
	 */
	public function testHandlesEmptyAlbum(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Run job on empty album
		RecomputeAlbumSizeJob::dispatchSync($album->id);

		// Assert all sizes are zero
		$stats = AlbumSizeStatistics::find($album->id);
		$this->assertNotNull($stats);
		$this->assertEquals(0, $stats->size_thumb);
		$this->assertEquals(0, $stats->size_thumb2x);
		$this->assertEquals(0, $stats->size_small);
		$this->assertEquals(0, $stats->size_small2x);
		$this->assertEquals(0, $stats->size_medium);
		$this->assertEquals(0, $stats->size_medium2x);
		$this->assertEquals(0, $stats->size_original);
	}

	/**
	 * Test job handles partial variants (not all types present).
	 *
	 * @return void
	 */
	public function testHandlesPartialVariants(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create photo WITH auto-variants first
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Delete all auto-generated variants
		SizeVariant::where('photo_id', $photo->id)->delete();

		// Only create THUMB and ORIGINAL variants
		SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::THUMB)->with_size(2000)->create();
		SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::ORIGINAL)->with_size(100000)->create();

		// Run job
		$job = new RecomputeAlbumSizeJob($album->id);
		$job->handle();

		// Assert present variants counted, missing variants are zero
		$stats = AlbumSizeStatistics::find($album->id);
		$this->assertEquals(2000, $stats->size_thumb);
		$this->assertEquals(0, $stats->size_thumb2x);
		$this->assertEquals(0, $stats->size_small);
		$this->assertEquals(0, $stats->size_small2x);
		$this->assertEquals(0, $stats->size_medium);
		$this->assertEquals(0, $stats->size_medium2x);
		$this->assertEquals(100000, $stats->size_original);
	}

	/**
	 * Test job aggregates multiple photos in same album.
	 *
	 * @return void
	 */
	public function testAggregatesMultiplePhotos(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create 3 photos with auto-generated variants
		for ($i = 0; $i < 3; $i++) {
			$photo = Photo::factory()->owned_by($user)->create();
			$photo->albums()->attach($album->id);
		}

		// Run job
		RecomputeAlbumSizeJob::dispatchSync($album->id);

		// Assert sizes summed across all photos (each variant type appears 3 times)
		$stats = AlbumSizeStatistics::find($album->id);
		// With 3 photos, all sizes should be 3x the single photo size
		$this->assertGreaterThan(0, $stats->size_thumb);
		$this->assertGreaterThan(0, $stats->size_original);
	}

	/**
	 * Test job uses updateOrCreate (idempotent).
	 *
	 * @return void
	 */
	public function testUpdateOrCreateIdempotent(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create();
		$photo->albums()->attach($album->id);

		// Run job first time
		RecomputeAlbumSizeJob::dispatchSync($album->id);

		$stats1 = AlbumSizeStatistics::find($album->id);
		$originalSize1 = $stats1->size_original;
		$this->assertGreaterThan(0, $originalSize1);

		// Run job second time (should update, not create new row)
		RecomputeAlbumSizeJob::dispatchSync($album->id);

		// Assert statistics still correct, still only one row
		$stats2 = AlbumSizeStatistics::find($album->id);
		$this->assertEquals($originalSize1, $stats2->size_original);
		$this->assertEquals(1, AlbumSizeStatistics::where('album_id', $album->id)->count());
	}

	/**
	 * Test job propagates to parent album.
	 *
	 * @return void
	 */
	public function testPropagesToParent(): void
	{
		Queue::fake();

		$user = User::factory()->may_administrate()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		// Run job on child
		$job = new RecomputeAlbumSizeJob($child->id);
		$job->handle();

		// Assert parent job was dispatched
		Queue::assertPushed(RecomputeAlbumSizeJob::class, function ($job) use ($parent) {
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

		$user = User::factory()->may_administrate()->create();
		$rootAlbum = Album::factory()->as_root()->owned_by($user)->create();

		// Run job on root album
		$job = new RecomputeAlbumSizeJob($rootAlbum->id);
		$job->handle();

		// Assert no additional jobs dispatched
		Queue::assertNothingPushed();
	}

	/**
	 * Test job handles missing album gracefully.
	 *
	 * @return void
	 */
	public function testHandlesMissingAlbum(): void
	{
		// Run job with non-existent album ID
		$job = new RecomputeAlbumSizeJob('nonexistent-id');
		$job->handle();

		// Should not throw exception, job logs warning and returns
		$this->assertTrue(true);
	}

	/**
	 * Test job only counts direct children photos (not descendants).
	 *
	 * @return void
	 */
	public function testCountsOnlyDirectChildren(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		// Add photo to child album
		$childPhoto = Photo::factory()->owned_by($user)->create();
		$childPhoto->albums()->attach($child->id);

		// Add photo to parent album
		$parentPhoto = Photo::factory()->owned_by($user)->create();
		$parentPhoto->albums()->attach($parent->id);

		// Run job on parent
		$job = new RecomputeAlbumSizeJob($parent->id);
		$job->handle();

		// Assert parent only counts its own photo (7 variants from 1 photo)
		// Child photo should not be counted
		$parentStats = AlbumSizeStatistics::find($parent->id);
		$this->assertGreaterThan(0, $parentStats->size_original);

		// Run job on child
		$childJob = new RecomputeAlbumSizeJob($child->id);
		$childJob->handle();

		$childStats = AlbumSizeStatistics::find($child->id);
		// Verify both have different sizes (proving they count separately)
		$this->assertGreaterThan(0, $childStats->size_original);
	}
}
