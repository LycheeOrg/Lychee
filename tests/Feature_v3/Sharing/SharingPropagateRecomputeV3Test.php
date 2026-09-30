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

use App\Events\AccessPermissionChanged;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 076 – recompute on permission changes (FR-076-13, S-076-18,
 * S-076-19, NFR-076-07).
 */
class SharingPropagateRecomputeV3Test extends BaseApiWithDataTest
{
	/**
	 * @return array<int,int|null>
	 */
	private function keys(Album $album): array
	{
		return AlbumUserThumb::query()->where('album_id', '=', $album->id)->where('is_precomputed', '=', true)
			->pluck('user_id')->all();
	}

	/**
	 * S-076-19: propagating (update and overwrite) dispatches
	 * AccessPermissionChanged once per touched descendant.
	 */
	public function testPropagateDispatchesOneEventPerDescendant(): void
	{
		$grand_child = Album::factory()->children_of($this->subAlbum1)->owned_by($this->userMayUpload1)->create();

		foreach ([false, true] as $shall_override) {
			Event::fake([AccessPermissionChanged::class]);
			$this->assertNoContent($this->actingAs($this->userMayUpload1)->putJson('Sharing', [
				'album_id' => $this->album1->id,
				'shall_override' => $shall_override,
			]));

			Event::assertDispatchedTimes(AccessPermissionChanged::class, 2);
			foreach ([$this->subAlbum1, $grand_child] as $descendant) {
				Event::assertDispatched(AccessPermissionChanged::class, fn (AccessPermissionChanged $e) => $e->base_album_id === $descendant->id);
			}
		}
	}

	/**
	 * S-076-18: sharing a single-share album with a second user re-keys the
	 * least-privilege row to `NULL`, and the second user's listing shows it;
	 * removing that share keys it back on the first user.
	 */
	public function testSecondShareReKeysTheLeastRow(): void
	{
		$first = User::factory()->create();
		$second = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload2)->create();
		Photo::factory()->owned_by($this->userMayUpload2)->in($album)->create();

		$this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$first->id], 'group_ids' => [], 'album_ids' => [$album->id],
			'grants_edit' => false, 'grants_delete' => false, 'grants_download' => false,
			'grants_full_photo_access' => false, 'grants_upload' => false, 'grants_move' => false,
		])->assertOk();
		self::assertEqualsCanonicalizing([$first->id, $this->userMayUpload2->id], $this->keys($album));

		$this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$second->id], 'group_ids' => [], 'album_ids' => [$album->id],
			'grants_edit' => false, 'grants_delete' => false, 'grants_download' => false,
			'grants_full_photo_access' => false, 'grants_upload' => false, 'grants_move' => false,
		])->assertOk();
		self::assertEqualsCanonicalizing([null, $this->userMayUpload2->id], $this->keys($album));

		$json = $this->actingAs($second)->getJsonV3('Albums/root?scope=shared')->assertOk()->json();
		$idx = array_search($album->id, $json['ids'], true);
		self::assertNotFalse($idx);
		self::assertNotNull($json['cover_ids'][$idx]);

		$perm = AccessPermission::query()->where('base_album_id', '=', $album->id)->where('user_id', '=', $second->id)->firstOrFail();
		$this->assertNoContent($this->actingAs($this->userMayUpload2)->deleteJson('Sharing', ['perm_id' => $perm->id]));
		self::assertEqualsCanonicalizing([$first->id, $this->userMayUpload2->id], $this->keys($album));
	}
}
