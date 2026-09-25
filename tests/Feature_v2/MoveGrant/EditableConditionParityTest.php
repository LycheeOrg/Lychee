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
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 072 — the SQL editability condition equals `AlbumPolicy::canEdit()`
 * for every album (FR-072-31, NFR-072-02, S-072-19).
 */
class EditableConditionParityTest extends BaseApiWithDataTest
{
	private function assertParity(User $user): void
	{
		$this->actingAs($user);

		/** @var AlbumQueryPolicy $policy */
		$policy = resolve(AlbumQueryPolicy::class);
		$query = Album::query()->select('albums.id');
		$policy->appendEditableCondition($query, $user);
		$sql_editable = $query->toBase()->pluck('id')->sort()->values()->all();

		$gate_editable = Album::query()->get()
			->filter(fn (Album $a) => Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $a]))
			->pluck('id')->sort()->values()->all();

		self::assertSame($gate_editable, $sql_editable, 'SQL editability diverges from AlbumPolicy::canEdit() for user ' . $user->id);
	}

	public function testOwnerWithUpload(): void
	{
		$this->assertParity($this->userMayUpload1);
	}

	public function testOwnerWithoutUpload(): void
	{
		$this->assertParity($this->userNoUpload);
	}

	public function testUserGrant(): void
	{
		$this->assertParity($this->userMayUpload2);
	}

	public function testGroupGrant(): void
	{
		$this->assertParity($this->userWithGroup1);
	}

	public function testPublicEditGrant(): void
	{
		AccessPermission::query()->where('id', '=', $this->perm4->id)->update(['grants_edit' => true]);
		$this->assertParity($this->userNoUpload);
	}

	public function testEditOnlyUserOnOneAlbum(): void
	{
		$user = User::factory()->create();
		AccessPermission::factory()->for_user($user)->for_album($this->subAlbum2)->visible()->grants_edit()->create();
		$this->assertParity($user);
	}

	public function testAdmin(): void
	{
		$this->assertParity($this->admin);
	}
}
