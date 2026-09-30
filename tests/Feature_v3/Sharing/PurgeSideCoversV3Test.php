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

use App\Actions\Sharing\PurgeAlbumUserThumbs;
use App\Models\AccessPermission;
use App\Models\AlbumUserThumb;
use App\Models\Photo;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 075 – side covers and the ADR-0010 purge invariant / FK behaviour
 * (FR-075-01, FR-075-08, FR-075-11; S-075-13, S-075-14).
 * Feature 076 – the purges leave regular-album precomputed rows alone
 * (FR-076-10; S-076-11, S-076-12).
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
	 * S-075-14: deleting a photo used as a side cover nulls the side column
	 * of the regular album's precomputed rows (Feature 076) and of the cache
	 * row; every row survives with its primary.
	 */
	public function testDeletingASidePhotoNullsSideColumnsAndKeepsCacheRow(): void
	{
		// Keep the PhotoDeleted-triggered RecomputeAlbumStatsJob from
		// refilling the side columns, so only the FK behaviour is observed.
		Queue::fake();
		$side = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->precomputedRow($this->album1->id, $this->userMayUpload1->id, $this->photo1->id, $side->id);
		$this->precomputedRow($this->album1->id, null, $this->photo1b->id, null, $side->id);
		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo1->id,
			'photo_id_2' => $side->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', ['photo_ids' => [$side->id], 'from_id' => $this->album1->id]);
		$this->assertNoContent($response);

		self::assertDatabaseHas('album_user_thumbs', ['album_id' => $this->album1->id, 'user_id' => $this->userMayUpload1->id, 'photo_id' => $this->photo1->id, 'photo_id_2' => null]);
		self::assertDatabaseHas('album_user_thumbs', ['album_id' => $this->album1->id, 'user_id' => null, 'photo_id' => $this->photo1b->id, 'photo_id_3' => null]);
		self::assertDatabaseHas('album_user_thumbs', [
			'album_id' => $this->tagAlbum1->id,
			'user_id' => $this->userMayUpload1->id,
			'photo_id' => $this->photo1->id,
			'photo_id_2' => null,
		]);
	}

	/**
	 * S-076-11: a group-membership change purges the user's cache rows but
	 * keeps the precomputed covers of the albums they own.
	 */
	public function testForUsersKeepsPrecomputedRows(): void
	{
		$this->precomputedRow($this->album1->id, $this->userMayUpload1->id, $this->photo1->id);
		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->photo1->id,
		]);

		resolve(PurgeAlbumUserThumbs::class)->forUsers([$this->userMayUpload1->id]);

		self::assertTrue($this->hasRow($this->album1->id, $this->userMayUpload1->id));
		self::assertFalse($this->hasRow($this->tagAlbum1->id, $this->userMayUpload1->id));
	}

	/**
	 * S-076-12: revoking a permission on a sub-album purges cache rows
	 * pointing at its photos, but keeps the parent's precomputed row whose
	 * cover comes from that sub-album.
	 */
	public function testRevocationKeepsPrecomputedRows(): void
	{
		$public_perm = AccessPermission::factory()->public()->visible()->for_album($this->subAlbum1)->create();
		$this->precomputedRow($this->album1->id, null, $this->subPhoto1->id);
		AlbumUserThumb::query()->create([
			'user_id' => $this->userNoUpload->id,
			'album_id' => $this->tagAlbum1->id,
			'photo_id' => $this->subPhoto1->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $public_perm->id]);
		$this->assertNoContent($response);

		self::assertTrue($this->hasRow($this->album1->id, null));
		self::assertFalse($this->hasRow($this->tagAlbum1->id, $this->userNoUpload->id));
	}

	private function precomputedRow(string $album_id, ?int $user_id, string $photo_id, ?string $photo_id_2 = null, ?string $photo_id_3 = null): void
	{
		AlbumUserThumb::query()->updateOrCreate(
			['album_id' => $album_id, 'user_id' => $user_id],
			['photo_id' => $photo_id, 'photo_id_2' => $photo_id_2, 'photo_id_3' => $photo_id_3, 'is_precomputed' => true],
		);
	}

	private function hasRow(string $album_id, ?int $user_id): bool
	{
		return AlbumUserThumb::query()->where('album_id', '=', $album_id)->where('user_id', $user_id)->exists();
	}
}
