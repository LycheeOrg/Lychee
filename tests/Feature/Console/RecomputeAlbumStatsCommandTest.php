<?php

/*
 * Copyright (C) 2025 Lychee contributors
 *
 * This file is part of Lychee.
 *
 * Lychee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Lychee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Lychee. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Tests\Feature\Console;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test recovery command (CLI-003-02).
 *
 * Verifies:
 * - Command dispatches RecomputeAlbumStatsJob correctly
 * - Sync mode executes immediately
 * - Async mode queues the job
 * - Invalid album_id is handled gracefully
 */
class RecomputeAlbumStatsCommandTest extends BasePrecomputingTest
{
	/**
	 * Test command dispatches job correctly for valid album.
	 */
	public function testCommandDispatchesJobForValidAlbum(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15'),
		]);
		$photo->albums()->attach($album->id);

		// Initially empty
		$this->assertEquals(0, $album->num_photos);

		// Run command in sync mode
		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		])
			->expectsOutput('Album stats recomputed successfully (sync).')
			->assertExitCode(0);

		$album->refresh();

		// Should be updated
		$this->assertEquals(1, $album->num_photos);
		$this->assertEquals('2023-06-15', $album->min_taken_at->format('Y-m-d'));
	}

	/**
	 * Test command handles invalid album_id gracefully.
	 */
	public function testCommandHandlesInvalidAlbumId(): void
	{
		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => 'invalid-id-that-does-not-exist',
		])
			->expectsOutput('Error: Album not found.')
			->assertExitCode(1);
	}

	/**
	 * Test async mode queues the job.
	 */
	public function testAsyncModeQueuesJob(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			// No --sync flag, should queue
		])
			->expectsOutput('Album stats recomputation job dispatched.')
			->assertExitCode(0);

		Queue::assertPushed(\App\Jobs\RecomputeAlbumStatsJob::class);
	}

	/**
	 * Test sync mode executes immediately.
	 */
	public function testSyncModeExecutesImmediately(): void
	{
		Queue::fake();

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

		// Job should NOT be queued (executed synchronously)
		Queue::assertNotPushed(\App\Jobs\RecomputeAlbumStatsJob::class);

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
			'taken_at' => new Carbon('2023-11-25'),
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
		$this->assertEquals('2023-11-25', $child->min_taken_at->format('Y-m-d'));
	}

	/**
	 * Test command can be used for manual recovery.
	 */
	public function testCommandCanBeUsedForManualRecovery(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-07-04'),
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
		$this->assertEquals('2023-07-04', $album->min_taken_at->format('Y-m-d'));
	}

	/**
	 * Test command output provides useful feedback.
	 */
	public function testCommandOutputProvidesUsefulFeedback(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$this->artisan('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		])
			->expectsOutput('Recomputing stats for album: ' . $album->id)
			->expectsOutput('Album stats recomputed successfully (sync).')
			->assertExitCode(0);
	}
}
