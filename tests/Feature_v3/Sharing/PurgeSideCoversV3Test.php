<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Sharing;

use App\Models\AccessPermission;
use App\Models\AlbumUserThumb;
use App\Models\Photo;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 075 – side covers and the ADR-0010 purge invariant / FK behaviour
 * (FR-075-01, FR-075-08, FR-075-11; S-075-13, S-075-14).
 */
class PurgeSideCoversV3Test extends BaseApiWithDataTest
{
	/**
	 * S-075-13: revoking access to an album whose photo is only a *side*
	 * cover in a viewer's cache row must still drop that row — a surviving
	 * row would keep serving the photo through the asset cover exception.
	 */
	public function testRevokingAccessPurgesRowsMatchingOnlyASideColumn(): void
	{
		$public_perm = AccessPermission::factory()->public()->visible()->for_album($this->album1)->create();

		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo2->id, // unrelated album
			'photo_id_2' => $this->photo1->id, // album1's photo, side rank only
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $public_perm->id]);
		$this->assertNoContent($response);

		self::assertSame(0, AlbumUserThumb::query()->where('album_id', '=', $this->tagAlbum1->id)->where('user_id', '=', $this->userNoUpload->id)->count());
	}

	/**
	 * S-075-14: deleting a photo used as a side cover nulls the `albums`
	 * side column and the cache side column; the cache row itself survives
	 * with its primary.
	 */
	public function testDeletingASidePhotoNullsSideColumnsAndKeepsCacheRow(): void
	{
		// Keep the PhotoDeleted-triggered RecomputeAlbumStatsJob from
		// refilling the side columns, so only the FK behaviour is observed.
		Queue::fake();
		$side = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->album1->auto_cover_id_max_privilege_2 = $side->id;
		$this->album1->auto_cover_id_least_privilege_3 = $side->id;
		$this->album1->save();
		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo1->id,
			'photo_id_2' => $side->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', ['photo_ids' => [$side->id], 'from_id' => $this->album1->id]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		self::assertNull($this->album1->auto_cover_id_max_privilege_2);
		self::assertNull($this->album1->auto_cover_id_least_privilege_3);
		self::assertDatabaseHas('album_user_thumbs', [
			'album_id' => $this->tagAlbum1->id,
			'user_id' => $this->userMayUpload1->id,
			'photo_id' => $this->photo1->id,
			'photo_id_2' => null,
		]);
	}
}
