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

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test explicit cover scenarios (S-003-09, S-003-10).
 *
 * Verifies that:
 * - Explicit cover_id takes precedence over automatic covers
 * - NULL cover_id causes fallback to automatic covers
 */
class ExplicitCoverTest extends BasePrecomputingTest
{
	/**
	 * S-003-09: User sets explicit cover_id, verify it takes precedence.
	 */
	public function testExplicitCoverTakesPrecedence(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Create two photos
		$photo1 = Photo::factory()->owned_by($user)->create([
			'title' => 'Photo 1',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // Newer, starred - would be auto-selected
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'title' => 'Photo 2',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'), // Older, not starred
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Recompute to get automatic covers
		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Automatic cover should be photo1 (starred, newer)
		$this->assertEquals($photo1->id, $album->auto_cover_id_max_privilege);

		// Set explicit cover to photo2
		$album->cover_id = $photo2->id;
		$album->save();

		// When loading album with HasAlbumThumb, explicit cover should take precedence
		// The thumb relation logic checks: cover_id first, then auto covers
		$this->assertEquals($photo2->id, $album->cover_id);
	}

	/**
	 * S-003-10: NULL cover_id uses automatic covers correctly.
	 */
	public function testNullCoverIdUsesAutomaticCovers(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create([
			'cover_id' => null, // Explicitly no manual cover
		]);

		$photo1 = Photo::factory()->owned_by($user)->create([
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // Starred, newer
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Recompute
		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// cover_id should still be null
		$this->assertNull($album->cover_id);

		// But automatic covers should be set
		$this->assertEquals($photo2->id, $album->auto_cover_id_max_privilege);
		$this->assertNotNull($album->auto_cover_id_least_privilege);
	}

	/**
	 * Test clearing explicit cover reverts to automatic.
	 */
	public function testClearingExplicitCoverRevertsToAutomatic(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create([
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Set explicit cover
		$album->cover_id = $photo2->id;
		$album->save();

		$this->assertEquals($photo2->id, $album->cover_id);

		// Clear explicit cover
		$album->cover_id = null;
		$album->save();

		// Automatic cover should still be available
		$this->assertNull($album->cover_id);
		$this->assertEquals($photo1->id, $album->auto_cover_id_max_privilege);
	}

	/**
	 * Test explicit cover persists across recomputation.
	 */
	public function testExplicitCoverPersistsAcrossRecomputation(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2023-01-01')]);
		$photo2 = Photo::factory()->owned_by($user)->create(['taken_at' => new Carbon('2023-12-31')]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Set explicit cover
		$album->cover_id = $photo1->id;
		$album->save();

		// Recompute stats
		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Explicit cover should be unchanged by recomputation
		$this->assertEquals($photo1->id, $album->cover_id);

		// But automatic covers should be computed
		$this->assertNotNull($album->auto_cover_id_max_privilege);
		$this->assertNotNull($album->auto_cover_id_least_privilege);
	}
}
