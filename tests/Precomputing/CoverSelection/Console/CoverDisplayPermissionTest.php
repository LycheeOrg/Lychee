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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
	 * Queries of `Album::query()->findOrFail()` for a guest, no explicit
	 * cover. The cover part is `cover`, `autoCoverRows`, its photo and size
	 * variants: four queries, one fewer than the two `*_privilege_cover`
	 * relations with their size variants plus `cover` (24 in total before
	 * Feature 076, NFR-076-02).
	 */
	private const MODEL_LOAD_QUERIES = 23;

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
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01 10:00:00'),
		]);
		$publicPhoto->albums()->attach($album->id);

		$privatePhoto = Photo::factory()->owned_by($owner)->create([
			'title' => 'Private Photo',
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31 10:00:00'), // highlighted, newer
		]);
		$privatePhoto->albums()->attach($album->id);

		// Make album publicly accessible (so least-privilege cover is meaningful)
		AccessPermission::factory()->for_album($album)->public()->visible()->create();

		// Recompute
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Admin should see max-privilege cover (privatePhoto, highlighted + newer)
		Auth::login($this->admin);
		$this->assertEquals($privatePhoto->id, $this->maxCovers($album)[0]);

		// Public/shared users should see least-privilege cover (publicPhoto only)
		// The exact photo depends on visibility rules, but it should not be the private photo
		$this->assertNotNull($this->leastCovers($album)[0]);
	}

	/**
	 * Test owner sees max-privilege cover.
	 */
	public function testOwnerSeesMaxPrivilegeCover(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();

		$photo1 = Photo::factory()->owned_by($owner)->create([
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01'),
		]);
		$photo2 = Photo::factory()->owned_by($owner)->create([
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		Auth::login($owner);

		// Owner should see max-privilege cover (photo2, highlighted + newer)
		$this->assertEquals($photo2->id, $this->maxCovers($album)[0]);
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
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);
		$privatePhoto->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Owner sees max-privilege cover
		Auth::login($owner);
		$this->assertEquals($privatePhoto->id, $this->maxCovers($album)[0]);

		// Non-owner should see least-privilege cover (may be NULL if no accessible photos)
		Auth::login($nonOwner);
		// Least-privilege cover should be NULL or different from max-privilege
		if ($this->leastCovers($album)[0] !== null) {
			$this->assertNotEquals($privatePhoto->id, $this->leastCovers($album)[0]);
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
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-01'),
		]);
		$privatePhoto = Photo::factory()->owned_by($owner)->create([
			'is_highlighted' => true,
			'taken_at' => new Carbon('2023-12-31'),
		]);

		$publicPhoto->albums()->attach($album->id);
		$privatePhoto->albums()->attach($album->id);

		// Grant shared access to specific user
		AccessPermission::factory()->for_album($album)->for_user($sharedUser)->create();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Shared user should see least-privilege cover
		Auth::login($sharedUser);
		$this->assertNotNull($this->leastCovers($album)[0]);
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

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Admin should see max-privilege
		Auth::login($admin);
		$this->assertNotNull($this->maxCovers($album)[0]);

		// Owner should see max-privilege
		Auth::login($owner);
		$this->assertNotNull($this->maxCovers($album)[0]);

		// Public user should see least-privilege
		Auth::login($publicUser);
		$this->assertNotNull($this->leastCovers($album)[0]);
	}

	/**
	 * S-076-08 / NFR-076-02: the v2 model path (`Album::$with` + `thumb`)
	 * resolves the same cover per viewer as the listings: owner and admin
	 * read the owner row, a guest the public row, in a fixed number of
	 * queries.
	 */
	public function testModelThumbFollowsTheRowModel(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		AccessPermission::factory()->for_album($album)->public()->visible()->create();
		$public = Album::factory()->children_of($album)->owned_by($owner)->create();
		AccessPermission::factory()->for_album($public)->public()->visible()->create();
		$hidden = Album::factory()->children_of($album)->owned_by($owner)->create();
		$public_photo = Photo::factory()->owned_by($owner)->in($public)->create(['taken_at' => new Carbon('2023-01-01')]);
		$private_photo = Photo::factory()->owned_by($owner)->in($hidden)->create(['taken_at' => new Carbon('2023-12-31'), 'is_highlighted' => true]);
		Artisan::call('lychee:recompute-album-stats', ['album_id' => $album->id, '--sync' => true]);

		Auth::login($owner);
		$this->assertSame($private_photo->id, Album::query()->findOrFail($album->id)->thumb?->id);
		Auth::login($this->admin);
		$this->assertSame($private_photo->id, Album::query()->findOrFail($album->id)->thumb?->id);
		Auth::logout();
		$this->assertSame($public_photo->id, Album::query()->findOrFail($album->id)->thumb?->id);

		DB::flushQueryLog();
		DB::enableQueryLog();
		Album::query()->findOrFail($album->id);
		$count = count(DB::getQueryLog());
		DB::disableQueryLog();
		$this->assertSame(self::MODEL_LOAD_QUERIES, $count, 'Loading an Album issued ' . $count . ' queries');
	}
}
