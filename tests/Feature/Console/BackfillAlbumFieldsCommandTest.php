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
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test backfill command (FR-003-06, S-003-12).
 *
 * Verifies:
 * - Command backfills computed fields for all albums
 * - Idempotency (safe to re-run)
 * - Dry-run mode
 * - Chunking works correctly
 */
class BackfillAlbumFieldsCommandTest extends BasePrecomputingTest
{
	/**
	 * Test command backfills computed values correctly.
	 */
	public function testBackfillComputesCorrectValues(): void
	{
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

		// Verify fields are initially null/0
		$album1->refresh();
		$album2->refresh();

		$this->assertEquals(0, $album1->num_photos);
		$this->assertEquals(0, $album2->num_photos);

		// Run backfill
		$this->artisan('lychee:backfill-album-fields')
			->expectsOutput('Backfill completed successfully.')
			->assertExitCode(0);

		// Verify fields are now populated
		$album1->refresh();
		$album2->refresh();

		$this->assertEquals(1, $album1->num_photos);
		$this->assertEquals(1, $album2->num_photos);
		$this->assertEquals('2023-01-15', $album1->min_taken_at->format('Y-m-d'));
		$this->assertEquals('2023-06-20', $album2->min_taken_at->format('Y-m-d'));
	}

	/**
	 * S-003-12: Test command is idempotent (can re-run safely).
	 */
	public function testBackfillIsIdempotent(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-03-10'),
		]);
		$photo->albums()->attach($album->id);

		// Run backfill first time
		$this->artisan('lychee:backfill-album-fields')
			->assertExitCode(0);

		$album->refresh();
		$firstNumPhotos = $album->num_photos;
		$firstMinTakenAt = $album->min_taken_at;

		// Run backfill second time
		$this->artisan('lychee:backfill-album-fields')
			->assertExitCode(0);

		$album->refresh();
		$secondNumPhotos = $album->num_photos;
		$secondMinTakenAt = $album->min_taken_at;

		// Values should be identical
		$this->assertEquals($firstNumPhotos, $secondNumPhotos);
		$this->assertEquals($firstMinTakenAt->format('Y-m-d H:i:s'), $secondMinTakenAt->format('Y-m-d H:i:s'));
	}

	/**
	 * Test dry-run mode does not modify database.
	 */
	public function testDryRunDoesNotModifyDatabase(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-05-15'),
		]);
		$photo->albums()->attach($album->id);

		$album->refresh();
		$beforeNumPhotos = $album->num_photos;

		// Run backfill in dry-run mode
		$this->artisan('lychee:backfill-album-fields', ['--dry-run' => true])
			->expectsOutput('Dry run completed.')
			->assertExitCode(0);

		$album->refresh();
		$afterNumPhotos = $album->num_photos;

		// Values should be unchanged
		$this->assertEquals($beforeNumPhotos, $afterNumPhotos);
		$this->assertEquals(0, $afterNumPhotos); // Still default value
	}

	/**
	 * Test chunking with custom chunk size.
	 */
	public function testChunkingWorksCorrectly(): void
	{
		$user = User::factory()->create();

		// Create 5 albums
		for ($i = 0; $i < 5; $i++) {
			$album = Album::factory()->as_root()->owned_by($user)->create();
			$photo = Photo::factory()->owned_by($user)->create();
			$photo->albums()->attach($album->id);
		}

		// Run backfill with small chunk size
		$this->artisan('lychee:backfill-album-fields', ['--chunk' => 2])
			->assertExitCode(0);

		// Verify all albums are processed
		$albums = Album::all();
		foreach ($albums as $album) {
			$this->assertEquals(1, $album->num_photos);
		}
	}

	/**
	 * Test command handles empty album gracefully.
	 */
	public function testBackfillHandlesEmptyAlbum(): void
	{
		$user = User::factory()->create();
		$emptyAlbum = Album::factory()->as_root()->owned_by($user)->create();

		$this->artisan('lychee:backfill-album-fields')
			->assertExitCode(0);

		$emptyAlbum->refresh();

		$this->assertEquals(0, $emptyAlbum->num_photos);
		$this->assertEquals(0, $emptyAlbum->num_children);
		$this->assertNull($emptyAlbum->min_taken_at);
		$this->assertNull($emptyAlbum->max_taken_at);
	}

	/**
	 * Test command processes nested albums correctly.
	 */
	public function testBackfillProcessesNestedAlbums(): void
	{
		$user = User::factory()->create();

		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-08-20'),
		]);
		$photo->albums()->attach($child->id);

		$this->artisan('lychee:backfill-album-fields')
			->assertExitCode(0);

		$parent->refresh();
		$child->refresh();

		// Child should have 1 photo
		$this->assertEquals(1, $child->num_photos);

		// Parent should have 1 child and inherit date range from child
		$this->assertEquals(1, $parent->num_children);
		$this->assertEquals('2023-08-20', $parent->min_taken_at->format('Y-m-d'));
	}
}
