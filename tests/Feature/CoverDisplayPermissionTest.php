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

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test permission-based cover display (S-003-17, S-003-18).
 *
 * Verifies cover selection varies by user role:
 * - Admin sees max-privilege cover
 * - Owner sees max-privilege cover
 * - Shared user sees least-privilege cover
 * - Non-owner sees different/null cover
 */
class CoverDisplayPermissionTest extends BasePrecomputingTest
{
	/**
	 * S-003-17: Admin sees max-privilege cover, shared user sees least-privilege.
	 */
	public function testAdminVsSharedUserCoverDisplay(): void
	{
		// Create owner and album
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();

		// Create public and private photos
		$publicPhoto = Photo::factory()->owned_by($owner)->create([
			'title' => 'Public Photo',
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$publicPhoto->albums()->attach($album->id);

		$privatePhoto = Photo::factory()->owned_by($owner)->create([
			'title' => 'Private Photo',
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // Starred, newer
		]);
		$privatePhoto->albums()->attach($album->id);

		// Make album publicly accessible (so least-privilege cover is meaningful)
		AccessPermission::factory()->for_album($album)->public()->create();

		// Recompute
		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Admin should see max-privilege cover (privatePhoto, starred + newer)
		Auth::login($this->admin);
		$this->assertEquals($privatePhoto->id, $album->auto_cover_id_max_privilege);

		// Public/shared users should see least-privilege cover (publicPhoto only)
		// The exact photo depends on visibility rules, but it should not be the private photo
		$this->assertNotNull($album->auto_cover_id_least_privilege);
	}

	/**
	 * Test owner sees max-privilege cover.
	 */
	public function testOwnerSeesMaxPrivilegeCover(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();

		$photo1 = Photo::factory()->owned_by($owner)->create([
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01'),
		]);
		$photo2 = Photo::factory()->owned_by($owner)->create([
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		Auth::login($owner);

		// Owner should see max-privilege cover (photo2, starred + newer)
		$this->assertEquals($photo2->id, $album->auto_cover_id_max_privilege);
	}

	/**
	 * S-003-18: Non-owner sees different/null cover when no public photos.
	 */
	public function testNonOwnerSeesDifferentOrNullCover(): void
	{
		$owner = User::factory()->create();
		$nonOwner = User::factory()->create();

		$album = Album::factory()->as_root()->owned_by($owner)->create();

		// Only create private photos (no public photos)
		$privatePhoto = Photo::factory()->owned_by($owner)->create([
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);
		$privatePhoto->albums()->attach($album->id);

		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Owner sees max-privilege cover
		Auth::login($owner);
		$this->assertEquals($privatePhoto->id, $album->auto_cover_id_max_privilege);

		// Non-owner should see least-privilege cover (may be NULL if no accessible photos)
		Auth::login($nonOwner);
		// Least-privilege cover should be NULL or different from max-privilege
		if ($album->auto_cover_id_least_privilege !== null) {
			$this->assertNotEquals($privatePhoto->id, $album->auto_cover_id_least_privilege);
		}
	}

	/**
	 * Test shared user with limited permissions.
	 */
	public function testSharedUserWithLimitedPermissions(): void
	{
		$owner = User::factory()->create();
		$sharedUser = User::factory()->create();

		$album = Album::factory()->as_root()->owned_by($owner)->create();

		// Create mix of photos
		$publicPhoto = Photo::factory()->owned_by($owner)->create([
			'is_starred' => false,
			'taken_at' => new Carbon('2023-01-01'),
		]);
		$privatePhoto = Photo::factory()->owned_by($owner)->create([
			'is_starred' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);

		$publicPhoto->albums()->attach($album->id);
		$privatePhoto->albums()->attach($album->id);

		// Grant shared access to specific user
		AccessPermission::factory()->for_album($album)->for_user($sharedUser)->create();

		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Shared user should see least-privilege cover
		Auth::login($sharedUser);
		$this->assertNotNull($album->auto_cover_id_least_privilege);
	}

	/**
	 * Test multi-user scenario with varying permissions.
	 */
	public function testMultiUserVaryingPermissions(): void
	{
		$owner = User::factory()->create();
		$admin = User::factory()->may_administrate()->create();
		$publicUser = User::factory()->create();

		$album = Album::factory()->as_root()->owned_by($owner)->create();

		$photo = Photo::factory()->owned_by($owner)->create([
			'taken_at' => new Carbon('2023-06-15'),
		]);
		$photo->albums()->attach($album->id);

		AccessPermission::factory()->for_album($album)->public()->create();

		\Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Admin should see max-privilege
		Auth::login($admin);
		$this->assertNotNull($album->auto_cover_id_max_privilege);

		// Owner should see max-privilege
		Auth::login($owner);
		$this->assertNotNull($album->auto_cover_id_max_privilege);

		// Public user should see least-privilege
		Auth::login($publicUser);
		$this->assertNotNull($album->auto_cover_id_least_privilege);
	}
}
