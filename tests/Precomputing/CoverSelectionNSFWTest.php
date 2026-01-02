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

namespace Tests\Precomputing;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test NSFW boundary scenarios for cover selection (S-003-14, S-003-15, S-003-16).
 *
 * Scenarios:
 * - S-003-14: Non-NSFW album with NSFW sub-album should exclude NSFW photos from parent covers
 * - S-003-15: NSFW album should allow NSFW photos in its covers
 * - S-003-16: NSFW parent context should apply to all child albums (children can use NSFW photos)
 */
class CoverSelectionNSFWTest extends BasePrecomputingTest
{
	/**
	 * Test S-003-14: Non-NSFW album excludes NSFW sub-album photos from covers.
	 */
	public function testNonNSFWAlbumExcludesNSFWSubAlbumPhotos(): void
	{
		Auth::login($this->admin);

		// Create safe parent album
		$safeParent = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'Safe Parent',
			'is_nsfw' => false,
		]);

		// Create NSFW child album
		$nsfwChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Child',
			'is_nsfw' => true,
		]);
		$nsfwChild->appendToNode($safeParent)->save();

		// Create safe photo in parent (older, not starred)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeParent->id);

		// Create NSFW photo in child (starred, newer - would be preferred if NSFW allowed)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwChild->id);

		// Make album publicly accessible for least-privilege computation
		AccessPermission::factory()->for_album($safeParent)->public()->create();

		// Recompute stats for parent (covers descendants)
		$job = new RecomputeAlbumStatsJob($safeParent->id);
		$job->handle();

		$safeParent->refresh();

		// Max-privilege cover can see all photos (including NSFW child)
		$this->assertEquals($nsfwPhoto->id, $safeParent->auto_cover_id_max_privilege, 'Max-privilege cover should include NSFW photo (starred, newer)');

		// Least-privilege cover should EXCLUDE NSFW child photos (parent is NOT in NSFW context)
		$this->assertEquals($safePhoto->id, $safeParent->auto_cover_id_least_privilege, 'Least-privilege cover should exclude NSFW sub-album photos when parent is not NSFW');
	}

	/**
	 * Test S-003-15: NSFW album allows NSFW photos in its covers.
	 */
	public function testNSFWAlbumAllowsNSFWPhotosInCovers(): void
	{
		Auth::login($this->admin);

		// Create NSFW parent album
		$nsfwParent = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'NSFW Parent',
			'is_nsfw' => true,
		]);

		// Create safe photo (older, not starred)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($nsfwParent->id);

		// Create NSFW photo in NSFW sub-album (newer, starred - should be preferred)
		$nsfwChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Child',
			'is_nsfw' => true,
		]);
		$nsfwChild->appendToNode($nsfwParent)->save();

		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwChild->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($nsfwParent)->public()->create();

		// Recompute stats
		$job = new RecomputeAlbumStatsJob($nsfwParent->id);
		$job->handle();

		$nsfwParent->refresh();

		// Both covers should prefer the NSFW photo (starred, newer)
		$this->assertEquals($nsfwPhoto->id, $nsfwParent->auto_cover_id_max_privilege, 'Max-privilege cover should prefer NSFW photo in NSFW album');
		$this->assertEquals($nsfwPhoto->id, $nsfwParent->auto_cover_id_least_privilege, 'Least-privilege cover should allow NSFW photo when album itself is NSFW');
	}

	/**
	 * Test S-003-16: NSFW parent context applies to all child albums.
	 */
	public function testNSFWParentContextAppliesToChildren(): void
	{
		Auth::login($this->admin);

		// Create NSFW grandparent
		$nsfwGrandparent = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'NSFW Grandparent',
			'is_nsfw' => true,
		]);

		// Create safe parent (child of NSFW grandparent)
		$safeParent = Album::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Parent',
			'is_nsfw' => false, // explicitly NOT NSFW itself
		]);
		$safeParent->appendToNode($nsfwGrandparent)->save();

		// Create safe child (grandchild of NSFW grandparent)
		$safeChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Child',
			'is_nsfw' => false,
		]);
		$safeChild->appendToNode($safeParent)->save();

		// Create safe photo in child
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeChild->id);

		// Create NSFW photo in nested child (starred, newer - would be preferred)
		$nsfwSubChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Sub-Child',
			'is_nsfw' => true,
		]);
		$nsfwSubChild->appendToNode($safeChild)->save();

		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwSubChild->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($safeParent)->public()->create();

		// Recompute stats for safe parent (should inherit NSFW context from grandparent)
		$job = new RecomputeAlbumStatsJob($safeParent->id);
		$job->handle();

		$safeParent->refresh();

		// Both covers should prefer NSFW photo (starred, newer) because parent is in NSFW context
		$this->assertEquals($nsfwPhoto->id, $safeParent->auto_cover_id_max_privilege, 'Max-privilege cover should prefer NSFW photo');
		$this->assertEquals($nsfwPhoto->id, $safeParent->auto_cover_id_least_privilege, 'Least-privilege cover should allow NSFW photo when parent album is in NSFW context (NSFW ancestor exists)');
	}

	/**
	 * Test complex NSFW hierarchy with multiple branches.
	 */
	public function testComplexNSFWHierarchy(): void
	{
		Auth::login($this->admin);

		// Root: Safe
		$root = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'Safe Root',
			'is_nsfw' => false,
		]);

		// Branch 1: NSFW child
		$nsfwBranch = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Branch',
			'is_nsfw' => true,
		]);
		$nsfwBranch->appendToNode($root)->save();

		// Branch 2: Safe child
		$safeBranch = Album::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Branch',
			'is_nsfw' => false,
		]);
		$safeBranch->appendToNode($root)->save();

		// Add NSFW photo to NSFW branch (starred, newer)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwBranch->id);

		// Add safe photo to safe branch (older, not starred)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeBranch->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($root)->public()->create();

		// Recompute root
		$job = new RecomputeAlbumStatsJob($root->id);
		$job->handle();

		$root->refresh();

		// Max-privilege should prefer NSFW photo (starred, newer)
		$this->assertEquals($nsfwPhoto->id, $root->auto_cover_id_max_privilege, 'Max-privilege cover should prefer NSFW photo from NSFW branch');

		// Least-privilege should only see safe branch photo (root is NOT in NSFW context)
		$this->assertEquals($safePhoto->id, $root->auto_cover_id_least_privilege, 'Least-privilege cover should exclude NSFW branch photos when root is not NSFW');
	}

	/**
	 * Test that NSFW filtering respects album-level NSFW status.
	 */
	public function testAlbumLevelNSFWFiltering(): void
	{
		Auth::login($this->admin);

		// Create safe album
		$album = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'Safe Album',
			'is_nsfw' => false,
		]);

		// Create safe photo
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($album->id);

		// Create NSFW sub-album
		$nsfwAlbum = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Album',
			'is_nsfw' => true,
		]);
		$nsfwAlbum->appendToNode($album)->save();

		// Create NSFW photo in NSFW sub-album (starred, newer)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwAlbum->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($album)->public()->create();

		// Recompute parent album
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		$album->refresh();

		// Max-privilege should prefer NSFW photo (starred, newer)
		$this->assertEquals($nsfwPhoto->id, $album->auto_cover_id_max_privilege);

		// Least-privilege should exclude NSFW photo (parent album is not NSFW)
		$this->assertEquals($safePhoto->id, $album->auto_cover_id_least_privilege);
	}
}
