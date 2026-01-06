<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test unified recompute/backfill command (FR-003-06, CLI-003-02, S-003-12).
 *
 * Verifies both modes:
 * - Single-album mode: Command recomputes stats for specific album (with album_id)
 * - Bulk backfill mode: Command backfills all albums (without album_id)
 */
class RecomputeAlbumStatsCommandTest extends BasePrecomputingTest
{
	// ============================================================
	// Single-Album Mode Tests
	// ============================================================

	/**
	 * Test command dispatches job correctly for valid album (async).
	 */
	public function testCommandDispatchesJobForValidAlbum(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			// No --sync flag, should queue
		])
			->expectsOutput('✓ Job dispatched to queue')
			->assertExitCode(0);

		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class);
	}

	/**
	 * Test command handles invalid album_id gracefully.
	 */
	public function testCommandHandlesInvalidAlbumId(): void
	{
		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => 'invalid-id-that-does-not-exist',
		])
			->expectsOutputToContain('not found')
			->assertExitCode(1);
	}

	/**
	 * Test sync mode executes immediately and updates album.
	 */
	public function testSyncModeExecutesImmediately(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-09-10'),
		]);
		$photo->albums()->attach($album->id);

		$this->assertEquals(0, $album->num_photos);

		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		])
			->assertExitCode(0);

		$album->refresh();

		// But album should be updated
		$this->assertEquals(1, $album->num_photos);
	}

	/**
	 * Test command works with nested albums.
	 */
	public function testCommandWorksWithNestedAlbums(): void
	{
		$user = User::factory()->create();

		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-11-25 10:00:00'),
		]);
		$photo->albums()->attach($child->id);

		// Recompute child
		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $child->id,
			'--sync' => true,
		])
			->assertExitCode(0);

		$child->refresh();

		$this->assertEquals(1, $child->num_photos);
		$this->assertEquals('2023-11-25', $child->min_taken_at->toDateString());
	}

	/**
	 * Test command can be used for manual recovery.
	 */
	public function testCommandCanBeUsedForManualRecovery(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-07-04 10:00:00'),
		]);
		$photo->albums()->attach($album->id);

		// Simulate stale data (manual corruption)
		$album->num_photos = 999;
		$album->min_taken_at = null;
		$album->saveQuietly(); // Skip events

		$this->assertEquals(999, $album->num_photos);

		// Use command to recover correct values
		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		])
			->assertExitCode(0);

		$album->refresh();

		// Should be corrected
		$this->assertEquals(1, $album->num_photos);
		$this->assertEquals('2023-07-04', $album->min_taken_at->toDateString());
	}

	// ============================================================
	// Bulk Backfill Mode Tests (without album_id)
	// ============================================================

	/**
	 * Test bulk mode backfills computed values correctly (S-003-12).
	 */
	public function testBulkBackfillComputesCorrectValues(): void
	{
		Queue::fake();

		$user = User::factory()->create();

		// Create albums with photos
		$album1 = Album::factory()->as_root()->owned_by($user)->create();
		$photo1 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-01-15'),
		]);
		$photo1->albums()->attach($album1->id);

		$album2 = Album::factory()->as_root()->owned_by($user)->create();
		$photo2 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-20'),
		]);
		$photo2->albums()->attach($album2->id);

		// Run bulk backfill (no album_id argument)
		$this->artisan('lychee:recompute-album-stats')
			->expectsOutputToContain('Starting album fields backfill')
			->expectsOutputToContain('Dispatched')
			->assertExitCode(0);

		// Verify jobs were dispatched for both albums
		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 2);
	}

	/**
	 * Test bulk backfill is idempotent (can re-run safely).
	 */
	public function testBulkBackfillIsIdempotent(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-03-10'),
		]);
		$photo->albums()->attach($album->id);

		// Run backfill first time
		$this->artisan('lychee:recompute-album-stats')
			->assertExitCode(0);

		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 1);

		Queue::fake(); // Reset queue

		// Run backfill second time
		$this->artisan('lychee:recompute-album-stats')
			->assertExitCode(0);

		// Should dispatch jobs again (idempotent = safe to re-run)
		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 1);
	}

	/**
	 * Test dry-run mode does not dispatch jobs.
	 */
	public function testDryRunDoesNotDispatchJobs(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-05-15'),
		]);
		$photo->albums()->attach($album->id);

		// Run backfill in dry-run mode
		$this->artisan('lychee:recompute-album-stats', ['--dry-run' => true])
			->expectsOutputToContain('DRY RUN MODE')
			->expectsOutputToContain('Would have dispatched')
			->assertExitCode(0);

		// No jobs should be dispatched
		Queue::assertNothingPushed();
	}

	/**
	 * Test chunking with custom chunk size.
	 */
	public function testChunkingWorksCorrectly(): void
	{
		Queue::fake();

		$user = User::factory()->create();

		// Create 5 albums
		for ($i = 0; $i < 5; $i++) {
			$album = Album::factory()->as_root()->owned_by($user)->create();
			$photo = Photo::factory()->owned_by($user)->create();
			$photo->albums()->attach($album->id);
		}

		// Run backfill with small chunk size
		$this->artisan('lychee:recompute-album-stats', ['--chunk' => 2])
			->assertExitCode(0);

		// Verify jobs dispatched for all 5 albums
		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 5);
	}

	/**
	 * Test bulk mode handles empty album gracefully.
	 */
	public function testBulkBackfillHandlesEmptyAlbum(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$emptyAlbum = Album::factory()->as_root()->owned_by($user)->create();

		$this->artisan('lychee:recompute-album-stats')
			->assertExitCode(0);

		// Job should still be dispatched for empty album
		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 1);
	}

	/**
	 * Test bulk mode processes nested albums correctly.
	 */
	public function testBulkBackfillProcessesNestedAlbums(): void
	{
		Queue::fake();

		$user = User::factory()->create();

		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-08-20'),
		]);
		$photo->albums()->attach($child->id);

		$this->artisan('lychee:recompute-album-stats')
			->assertExitCode(0);

		// Jobs should be dispatched for both parent and child
		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class, 2);
	}

	/**
	 * Test bulk mode handles no albums gracefully.
	 */
	public function testBulkBackfillHandlesNoAlbums(): void
	{
		Queue::fake();

		// No albums in database
		$this->artisan('lychee:recompute-album-stats')
			->expectsOutputToContain('No albums to process')
			->assertExitCode(0);

		Queue::assertNothingPushed();
	}

	/**
	 * Test invalid chunk size is rejected.
	 */
	public function testInvalidChunkSizeIsRejected(): void
	{
		$this->artisan('lychee:recompute-album-stats', ['--chunk' => 0])
			->expectsOutputToContain('Chunk size must be at least 1')
			->assertExitCode(1);
	}
}
