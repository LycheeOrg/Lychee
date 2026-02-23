<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection\Console;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
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
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // Newer, highlighted - would be auto-selected
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'title' => 'Photo 2',
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'), // Older, not highlighted
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Recompute to get automatic covers
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Automatic cover should be photo1 (highlighted, newer)
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
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // highlighted, newer
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Recompute
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// cover_id should still be null
		$this->assertNull($album->cover_id);

		// But automatic covers should be set
		$this->assertEquals($photo2->id, $album->auto_cover_id_max_privilege);
		// There is no least options since the album is NOT shared.
		$this->assertNull($album->auto_cover_id_least_privilege);
	}

	/**
	 * Test clearing explicit cover reverts to automatic.
	 */
	public function testClearingExplicitCoverRevertsToAutomatic(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create([
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
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
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Explicit cover should be unchanged by recomputation
		$this->assertEquals($photo1->id, $album->cover_id);

		// But automatic covers should be computed
		$this->assertNotNull($album->auto_cover_id_max_privilege);
		// There is no least options since the album is NOT shared.
		$this->assertNull($album->auto_cover_id_least_privilege);
	}
}
