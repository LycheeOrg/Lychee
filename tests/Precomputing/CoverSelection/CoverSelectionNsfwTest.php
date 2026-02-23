<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection;

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
class CoverSelectionNsfwTest extends BasePrecomputingTest
{
	/**
	 * Test S-003-14: Non-NSFW album excludes NSFW sub-album photos from covers.
	 */
	public function testNonNsfwAlbumExcludesNsfwSubAlbumPhotos(): void
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

		// Create safe photo in parent (older, not highlighted)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeParent->id);

		// Create NSFW photo in child (highlighted, newer - would be preferred if NSFW allowed)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwChild->id);

		// Make album publicly accessible for least-privilege computation
		AccessPermission::factory()->for_album($safeParent)->public()->visible()->create();
		AccessPermission::factory()->for_album($nsfwChild)->public()->visible()->create();

		// Recompute stats for parent (covers descendants)
		$job = new RecomputeAlbumStatsJob($safeParent->id);
		$job->handle();

		$safeParent->refresh();

		// Safe album (no NSFW parent) should ALWAYS exclude NSFW photos for BOTH privilege levels
		$this->assertEquals($safePhoto->id, $safeParent->auto_cover_id_max_privilege, 'Max-privilege cover should ALSO exclude NSFW sub-album photos when parent is safe (no NSFW parent)');
		$this->assertEquals($safePhoto->id, $safeParent->auto_cover_id_least_privilege, 'Least-privilege cover should exclude NSFW sub-album photos when parent is safe (no NSFW parent)');
	}

	/**
	 * Test S-003-15: NSFW album allows NSFW photos in its covers.
	 */
	public function testNsfwAlbumAllowsNsfwPhotosInCovers(): void
	{
		Auth::login($this->admin);

		// Create NSFW parent album
		$nsfwParent = Album::factory()->as_root()->owned_by($this->admin)->create([
			'title' => 'NSFW Parent',
			'is_nsfw' => true,
		]);

		// Create safe photo (older, not highlighted)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($nsfwParent->id);

		// Create NSFW photo in NSFW sub-album (newer, highlighted - should be preferred)
		$nsfwChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Child',
			'is_nsfw' => true,
		]);
		$nsfwChild->appendToNode($nsfwParent)->save();

		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwChild->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($nsfwParent)->public()->visible()->create();
		AccessPermission::factory()->for_album($nsfwChild)->public()->visible()->create();

		// Recompute stats
		$job = new RecomputeAlbumStatsJob($nsfwParent->id);
		$job->handle();

		$nsfwParent->refresh();

		// Both covers should prefer the NSFW photo (highlighted, newer)
		$this->assertEquals($nsfwPhoto->id, $nsfwParent->auto_cover_id_max_privilege, 'Max-privilege cover should prefer NSFW photo in NSFW album');
		$this->assertEquals($nsfwPhoto->id, $nsfwParent->auto_cover_id_least_privilege, 'Least-privilege cover should allow NSFW photo when album itself is NSFW');
	}

	/**
	 * Test S-003-16: NSFW parent context applies to all child albums.
	 */
	public function testNsfwParentContextAppliesToChildren(): void
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
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeChild->id);

		// Create NSFW photo in nested child (highlighted, newer - would be preferred)
		$nsfwSubChild = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Sub-Child',
			'is_nsfw' => true,
		]);
		$nsfwSubChild->appendToNode($safeChild)->save();

		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwSubChild->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($safeParent)->public()->visible()->create();
		AccessPermission::factory()->for_album($safeChild)->public()->visible()->create();
		AccessPermission::factory()->for_album($nsfwSubChild)->public()->visible()->create();

		// Recompute stats for safe parent (should inherit NSFW context from grandparent)
		$job = new RecomputeAlbumStatsJob($safeParent->id);
		$job->handle();

		$safeParent->refresh();

		// Both covers should prefer NSFW photo (highlighted, newer) because parent is in NSFW context
		$this->assertEquals($nsfwPhoto->id, $safeParent->auto_cover_id_max_privilege, 'Max-privilege cover should prefer NSFW photo');
		$this->assertEquals($nsfwPhoto->id, $safeParent->auto_cover_id_least_privilege, 'Least-privilege cover should allow NSFW photo when parent album is in NSFW context (NSFW ancestor exists)');
	}

	/**
	 * Test complex NSFW hierarchy with multiple branches.
	 */
	public function testComplexNsfwHierarchy(): void
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

		// Add NSFW photo to NSFW branch (highlighted, newer)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwBranch->id);

		// Add safe photo to safe branch (older, not highlighted)
		$safePhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'Safe Photo',
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($safeBranch->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($root)->public()->visible()->create();
		AccessPermission::factory()->for_album($safeBranch)->public()->visible()->create();
		AccessPermission::factory()->for_album($nsfwBranch)->public()->visible()->create();

		// Recompute root
		$job = new RecomputeAlbumStatsJob($root->id);
		$job->handle();

		$root->refresh();

		// Safe root (no NSFW parent) should ALWAYS exclude NSFW photos for BOTH privilege levels
		$this->assertEquals($safePhoto->id, $root->auto_cover_id_max_privilege, 'Max-privilege cover should ALSO exclude NSFW branch photos when root is safe (no NSFW parent)');
		$this->assertEquals($safePhoto->id, $root->auto_cover_id_least_privilege, 'Least-privilege cover should exclude NSFW branch photos when root is safe (no NSFW parent)');
	}

	/**
	 * Test that NSFW filtering respects album-level NSFW status.
	 */
	public function testAlbumLevelNsfwFiltering(): void
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
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$safePhoto->albums()->attach($album->id);

		// Create NSFW sub-album
		$nsfwAlbum = Album::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Album',
			'is_nsfw' => true,
		]);
		$nsfwAlbum->appendToNode($album)->save();

		// Create NSFW photo in NSFW sub-album (highlighted, newer)
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'title' => 'NSFW Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'),
		]);
		$nsfwPhoto->albums()->attach($nsfwAlbum->id);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($album)->public()->visible()->create();
		AccessPermission::factory()->for_album($nsfwAlbum)->public()->visible()->create();

		// Recompute parent album
		$job = new RecomputeAlbumStatsJob($album->id);
		$job->handle();

		$album->refresh();

		// Safe album (no NSFW parent) should ALWAYS exclude NSFW photos for BOTH privilege levels
		$this->assertEquals($safePhoto->id, $album->auto_cover_id_max_privilege, 'Max-privilege cover should ALSO exclude NSFW photos when album is safe (no NSFW parent)');
		$this->assertEquals($safePhoto->id, $album->auto_cover_id_least_privilege, 'Least-privilege cover should exclude NSFW photos when album is safe (no NSFW parent)');
	}
}
