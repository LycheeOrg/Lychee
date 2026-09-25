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

namespace Tests\Feature_v2\MoveGrant;

use App\Contracts\Models\AbstractAlbum;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 072 — aggregate (`…ById`) authorization (Q-072-12).
 *
 * Parity: every aggregate check agrees with its per-model policy counterpart,
 * for every album/photo of the fixture and a spread of users.
 * Cost: authorizing a batch issues the same number of grant queries as
 * authorizing a single item.
 */
class AggregateAuthorizationTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
		// A spread of grants: move-only on subAlbum1, edit-only on album2,
		// full + download on album1, public full + download on album4.
		$this->grant($this->subAlbum1, ['move']);
		$this->grant($this->album2, ['edit']);
		$this->grant($this->album1, ['full', 'download']);
		AccessPermission::query()->where('id', '=', $this->perm4->id)->update(['grants_full_photo_access' => true, 'grants_download' => true]);
	}

	/**
	 * @return User[]
	 */
	private function users(): array
	{
		return [$this->userMayUpload1, $this->userMayUpload2, $this->userNoUpload, $this->userWithGroup1, $this->attacker];
	}

	public function testAlbumAggregatesMatchPolicy(): void
	{
		foreach ($this->users() as $user) {
			$this->actingAs($user);
			/** @var AlbumPolicy $policy */
			$policy = resolve(AlbumPolicy::class);
			foreach (Album::query()->get() as $album) {
				$album = Album::query()->findOrFail($album->id);
				self::assertSame(
					Gate::check(AlbumPolicy::CAN_MOVE_ALBUM, [AbstractAlbum::class, $album]),
					$policy->canMoveAlbumsById($user, [$album->id]),
					"canMoveAlbumsById diverges for user {$user->id}, album {$album->id}"
				);
				self::assertSame(
					Gate::check(AlbumPolicy::CAN_MOVE, [AbstractAlbum::class, $album]),
					$policy->canMoveContentById($user, [$album->id]),
					"canMoveContentById diverges for user {$user->id}, album {$album->id}"
				);
			}
		}
	}

	public function testPhotoAggregatesMatchPolicy(): void
	{
		foreach ($this->users() as $user) {
			$this->actingAs($user);
			/** @var PhotoPolicy $policy */
			$policy = resolve(PhotoPolicy::class);
			foreach (Photo::query()->get() as $photo) {
				$photo = Photo::query()->findOrFail($photo->id);
				self::assertSame(
					Gate::check(PhotoPolicy::CAN_MOVE, [Photo::class, $photo]),
					$policy->canMoveById($user, [$photo->id]),
					"canMoveById diverges for user {$user->id}, photo {$photo->id}"
				);
				self::assertSame(
					Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]) && Gate::check(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $photo]),
					$policy->canAccessFullAndDownloadById($user, [$photo->id]),
					"canAccessFullAndDownloadById diverges for user {$user->id}, photo {$photo->id}"
				);
			}
		}
	}

	/**
	 * Counts the authorization queries: those filtering on a `grants_*` column.
	 * Model hydration (eager loads of `access_permissions`, covers, …) happens
	 * per request and is not part of the authorization cost.
	 */
	private function countPermissionQueries(\Closure $callback): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$callback();
		$count = count(array_filter(
			DB::getQueryLog(),
			fn (array $q) => preg_match('/\bgrants_(move|delete|edit|download|full_photo_access)\b\s*=/', str_replace(['"', '`'], '', $q['query'])) === 1
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		return $count;
	}

	/** Copying 1 or 4 photos costs the same number of grant queries. */
	public function testPhotoCopyAuthorizationCostIsIndependentOfBatchSize(): void
	{
		$this->grant($this->album1, ['move', 'full', 'download']);
		$photos = Photo::factory()->count(3)->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$target = Album::factory()->as_root()->owned_by($this->attacker)->create();
		$this->actingAs($this->attacker);

		$one = $this->countPermissionQueries(fn () => $this->postJson('Photo::copy', ['photo_ids' => [$this->photo1->id], 'album_id' => $this->attacker_album->id])->assertNoContent());
		$four = $this->countPermissionQueries(fn () => $this->postJson('Photo::copy', ['photo_ids' => [$this->photo1->id, ...$photos->pluck('id')->all()], 'album_id' => $target->id])->assertNoContent());

		self::assertSame($one, $four);
	}

	/** Moving 1 or 3 albums costs the same number of grant queries. */
	public function testAlbumMoveAuthorizationCostIsIndependentOfBatchSize(): void
	{
		$this->grant($this->album1, ['move']);
		$more = Album::factory()->count(3)->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$target_one = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$target_many = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->grant($target_one, ['edit']);
		$this->grant($target_many, ['edit']);
		$this->actingAs($this->attacker);

		$one = $this->countPermissionQueries(fn () => $this->postJson('Album::move', ['album_id' => $target_one->id, 'album_ids' => [$this->subAlbum1->id]])->assertNoContent());
		$three = $this->countPermissionQueries(fn () => $this->postJson('Album::move', ['album_id' => $target_many->id, 'album_ids' => $more->pluck('id')->all()])->assertNoContent());

		self::assertSame($one, $three);
	}
}
