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

namespace Tests\Feature_v3\Album;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 076 – lifecycle of a regular album's precomputed cover rows
 * across ownership changes and deletion (FR-076-09, FR-076-10;
 * S-076-10, S-076-16).
 */
class AutoCoverRowsV3Test extends BaseApiWithDataTest
{
	/**
	 * The `user_id`s of the album's precomputed cover rows, in no particular order.
	 *
	 * @return array<int,int|null>
	 */
	private function keys(Album $album): array
	{
		return AlbumUserThumb::query()->where('album_id', '=', $album->id)->where('is_precomputed', '=', true)
			->pluck('user_id')->all();
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	/**
	 * S-076-10: transferring an album with a sub-album to Y re-keys both
	 * owner rows to Y, and drops the single-share row Y held on the
	 * sub-album (the owner row now serves Y).
	 */
	public function testTransferReKeysOwnerRows(): void
	{
		AccessPermission::factory()->for_user($this->userLocked)->for_album($this->subAlbum1)->create();
		$this->recompute($this->subAlbum1);
		$this->recompute($this->album1);
		self::assertEqualsCanonicalizing([$this->userMayUpload1->id, $this->userLocked->id], $this->keys($this->subAlbum1));
		$owner_row_photo = AlbumUserThumb::query()->where('album_id', '=', $this->subAlbum1->id)->where('user_id', '=', $this->userMayUpload1->id)->value('photo_id');

		Queue::fake();
		$this->assertNoContent($this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userLocked->id,
		]));

		self::assertEqualsCanonicalizing([$this->userLocked->id], $this->keys($this->subAlbum1));
		self::assertSame($owner_row_photo, AlbumUserThumb::query()->where('album_id', '=', $this->subAlbum1->id)->where('user_id', '=', $this->userLocked->id)->value('photo_id'));
		self::assertContains($this->userLocked->id, $this->keys($this->album1));
		self::assertNotContains($this->userMayUpload1->id, $this->keys($this->album1));
	}

	/**
	 * S-076-10 (user deletion half): the deleted user's albums move to the
	 * acting admin together with their owner rows; the deleted user's
	 * single-share rows go with their permission.
	 */
	public function testUserDeletionReKeysOwnerRowsAndDropsSingleShareRows(): void
	{
		AccessPermission::factory()->for_user($this->userNoUpload)->for_album($this->album2)->create();
		$this->recompute($this->album2);
		$this->recompute($this->album3);
		self::assertEqualsCanonicalizing([$this->userMayUpload2->id, $this->userNoUpload->id], $this->keys($this->album2));
		self::assertEqualsCanonicalizing([$this->userNoUpload->id], $this->keys($this->album3));

		Queue::fake();
		$this->assertNoContent($this->actingAs($this->admin)->deleteJson('UserManagement', ['id' => $this->userNoUpload->id]));

		self::assertEqualsCanonicalizing([$this->admin->id], $this->keys($this->album3));
		self::assertEqualsCanonicalizing([$this->userMayUpload2->id], $this->keys($this->album2));
	}

	/**
	 * S-076-16: deleting a regular album deletes its precomputed rows, even
	 * when its cover photo survives (it also lives in another album, so the
	 * `photo_id` cascade does not fire).
	 */
	public function testAlbumDeletionDeletesPrecomputedRows(): void
	{
		$this->photo3->albums()->attach($this->album2->id);
		$this->recompute($this->album3);
		self::assertNotSame([], $this->keys($this->album3));

		Queue::fake();
		$this->assertNoContent($this->actingAs($this->admin)->deleteJson('Album', ['album_ids' => [$this->album3->id]]));

		self::assertEqualsCanonicalizing([], $this->keys($this->album3));
	}
}
