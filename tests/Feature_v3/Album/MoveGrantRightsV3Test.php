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
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 072 — v3 `/rights` move signals (FR-072-20, Q-072-09).
 *
 * The move grant covers an album's content: `can_move_children` comes from the
 * grant on the parent, `grants_move[i]` from the grant on child i itself.
 */
class MoveGrantRightsV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	private function makeParentWithChildren(): array
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$movable = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$editable = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		(new RecomputeAlbumStatsJob($movable->id, propagate_to_parent: false))->handle();
		(new RecomputeAlbumStatsJob($editable->id, propagate_to_parent: false))->handle();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($movable)->visible()->grants_move()->create();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($editable)->visible()->grants_edit()->create();

		return [$parent, $movable, $editable];
	}

	public function testGrantsMoveIsPerChildContent(): void
	{
		[$parent, $movable, $editable] = $this->makeParentWithChildren();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($parent)->visible()->create();

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();

		self::assertFalse($json['can_move_children'], 'no move grant on the parent');
		$idx_movable = array_search($movable->id, $json['ids'], true);
		$idx_editable = array_search($editable->id, $json['ids'], true);
		self::assertTrue($json['grants_move'][$idx_movable]);
		self::assertFalse($json['grants_move'][$idx_editable]);
	}

	public function testMoveGrantOnParentAllowsMovingChildren(): void
	{
		[$parent] = $this->makeParentWithChildren();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($parent)->visible()->grants_move()->create();

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();

		self::assertTrue($json['can_move_children']);
		self::assertFalse($json['can_delete_children']);
	}

	public function testAdminGetsEverythingTrue(): void
	{
		$json = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/rights")->assertOk()->json();
		self::assertTrue($json['can_move_children']);
		self::assertSame(array_fill(0, count($json['ids']), true), $json['grants_move']);
	}

	public function testRootRightsCarryGrantsMove(): void
	{
		$json = $this->actingAs($this->userMayUpload2)->getJsonV3('Albums/root/rights', ['scope' => 'shared'])->assertOk()->json();
		self::assertFalse($json['can_move_children']);
		$idx = array_search($this->album1->id, $json['ids'], true);
		self::assertNotFalse($idx);
		self::assertTrue($json['grants_move'][$idx]);
	}
}
