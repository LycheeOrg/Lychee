<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection\Console;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test album cover security scenarios (Feature 003 - Increment 11).
 *
 * Tests verify correct cover selection based on user roles and permissions:
 * - Admin users see max-privilege covers (including private photos)
 * - Shared users see least-privilege covers (respecting PhotoQueryPolicy)
 * - Non-owners see restricted covers (may be NULL if no accessible photos)
 * - NSFW boundaries are enforced in cover selection
 */
class AlbumCoverSecurityTest extends BasePrecomputingTest
{
	/**
	 * T-003-39: Test admin sees max-privilege cover with private photo.
	 *
	 * Create private album with private photo, verify admin user sees max-privilege
	 * cover (not null).
	 *
	 * @return void
	 */
	public function testAdminSeesMaxPrivilegeCover(): void
	{
		// Create admin user
		$admin = User::factory()->may_administrate()->create();
		Auth::login($admin);

		// Create a private album owned by a different user
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create([
			'title' => 'Private Album',
		]);

		// Create a private photo in the album
		$photo = Photo::factory()->owned_by($owner)->create([
			'title' => 'Private Photo',
		]);
		$photo->albums()->attach($album->id);

		// Trigger stats recomputation
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		// Reload album to get computed fields
		$album->refresh();

		// Assert admin sees max-privilege cover
		$this->assertNotNull($album->auto_cover_id_max_privilege, 'Admin should see max-privilege cover');
		$this->assertEquals($photo->id, $album->auto_cover_id_max_privilege, 'Max-privilege cover should be the private photo');
	}

	/**
	 * T-003-40: Test NSFW boundary scenarios.
	 *
	 * Test non-NSFW album excludes NSFW sub-album photos, NSFW album allows NSFW
	 * photos, NSFW parent context applies to children.
	 *
	 * @return void
	 */
	public function testNsfwBoundaries(): void
	{
		// Create user
		$user = User::factory()->create();
		Auth::login($user);

		// Create non-NSFW root album
		$rootAlbum = Album::factory()->as_root()->owned_by($user)->create([
			'title' => 'Safe Album',
			'is_nsfw' => false,
		]);

		// Create NSFW sub-album
		$nsfwAlbum = Album::factory()->owned_by($user)->create([
			'title' => 'NSFW Sub-Album',
			'is_nsfw' => true,
			'parent_id' => $rootAlbum->id,
		]);
		$nsfwAlbum->appendToNode($rootAlbum)->save();

		// Create photo in NSFW sub-album
		$nsfwPhoto = Photo::factory()->owned_by($user)->create([
			'title' => 'NSFW Photo',
		]);
		$nsfwPhoto->albums()->attach($nsfwAlbum->id);

		// Create safe photo in root album
		$safePhoto = Photo::factory()->owned_by($user)->create([
			'title' => 'Safe Photo',
		]);
		$safePhoto->albums()->attach($rootAlbum->id);

		// Make root album publicly accessible (so least-privilege cover is computed)
		AccessPermission::factory()->for_album($rootAlbum)->public()->create();

		// Trigger stats recomputation for both albums
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $nsfwAlbum->id,
			'--sync' => true,
		]);
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $rootAlbum->id,
			'--sync' => true,
		]);

		// Reload albums
		$rootAlbum->refresh();
		$nsfwAlbum->refresh();

		// Assert NSFW sub-album has NSFW photo as cover (in NSFW context)
		$this->assertEquals($nsfwPhoto->id, $nsfwAlbum->auto_cover_id_max_privilege, 'NSFW album should have NSFW photo as max-privilege cover');

		// Assert root album's least-privilege cover excludes NSFW photos (not in NSFW context)
		$this->assertEquals($safePhoto->id, $rootAlbum->auto_cover_id_least_privilege, 'Non-NSFW album least-privilege cover should exclude NSFW photos');
	}

	/**
	 * T-003-41: Test public album shows least-privilege cover.
	 *
	 * Create public album with photo, verify least-privilege cover is set.
	 *
	 * @return void
	 */
	public function testSharedUserSeesLeastPrivilegeCover(): void
	{
		// Create owner
		$owner = User::factory()->create();

		// Create public album
		$album = Album::factory()->as_root()->owned_by($owner)->create([
			'title' => 'Public Album',
		]);

		// Make album publicly accessible
		AccessPermission::factory()->for_album($album)->public()->create();

		// Create photo in album
		$photo = Photo::factory()->owned_by($owner)->create([
			'title' => 'Public Photo',
		]);
		$photo->albums()->attach($album->id);

		// Trigger stats recomputation
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		// Reload album
		$album->refresh();

		// Assert both covers exist and are the same (only one photo)
		$this->assertNotNull($album->auto_cover_id_max_privilege, 'Max-privilege cover should exist');
		$this->assertNotNull($album->auto_cover_id_least_privilege, 'Least-privilege cover should exist for public album');
		$this->assertEquals($photo->id, $album->auto_cover_id_least_privilege, 'Least-privilege cover should be the public photo');
	}

	/**
	 * T-003-42: Test non-owner sees different/null cover.
	 *
	 * Create album with private photos, verify non-owner restricted user sees
	 * least-privilege cover (may be NULL if no public photos).
	 *
	 * @return void
	 */
	public function testNonOwnerSeesDifferentCover(): void
	{
		// Create owner
		$owner = User::factory()->create();

		// Create private album (no access permissions = not accessible to public)
		$album = Album::factory()->as_root()->owned_by($owner)->create([
			'title' => 'Private Owner Album',
		]);

		// Create photos in the private album
		$photo1 = Photo::factory()->owned_by($owner)->create([
			'title' => 'Photo 1',
		]);
		$photo1->albums()->attach($album->id);

		$photo2 = Photo::factory()->owned_by($owner)->create([
			'title' => 'Photo 2',
		]);
		$photo2->albums()->attach($album->id);

		// Trigger stats recomputation
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		// Reload album
		$album->refresh();

		// Assert max-privilege cover exists (owner/admin can see photos)
		$this->assertNotNull($album->auto_cover_id_max_privilege, 'Max-privilege cover should exist for owner');

		// Assert least-privilege cover is NULL (album has no AccessPermissions, so nobody can access it)
		$this->assertNull($album->auto_cover_id_least_privilege, 'Least-privilege cover should be NULL when album has no access permissions');
	}

	/**
	 * Feature 075, NFR-075-03 / S-075-03: the least-privilege side covers are
	 * chosen by the same searchability filter as the least-privilege cover,
	 * so a private photo can only ever appear in the max-privilege ranks.
	 */
	public function testLeastPrivilegeSideCoversExcludePrivatePhotos(): void
	{
		$owner = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($owner)->create();
		$public_child = Album::factory()->children_of($root)->owned_by($owner)->create();
		$private_child = Album::factory()->children_of($root)->owned_by($owner)->create();
		AccessPermission::factory()->for_album($root)->public()->visible()->create();
		AccessPermission::factory()->for_album($public_child)->public()->visible()->create();

		$public_photos = Photo::factory()->in($public_child)->owned_by($owner)->count(2)->create()->pluck('id')->all();
		$private_photos = Photo::factory()->in($private_child)->owned_by($owner)->count(2)->create()->pluck('id')->all();

		Artisan::call('lychee:recompute-album-stats', ['album_id' => $root->id, '--sync' => true]);
		$root->refresh();

		$least = array_filter([$root->auto_cover_id_least_privilege, $root->auto_cover_id_least_privilege_2, $root->auto_cover_id_least_privilege_3]);
		$max = array_filter([$root->auto_cover_id_max_privilege, $root->auto_cover_id_max_privilege_2, $root->auto_cover_id_max_privilege_3]);

		$this->assertCount(2, $least, 'Only the two public photos qualify for the least-privilege ranks');
		$this->assertSame([], array_intersect($least, $private_photos), 'Least-privilege ranks must never contain a private photo');
		$this->assertEqualsCanonicalizing($public_photos, array_values($least));
		$this->assertCount(3, $max);
		$this->assertNotSame([], array_intersect($max, $private_photos), 'Max-privilege ranks may contain private photos');
	}
}
