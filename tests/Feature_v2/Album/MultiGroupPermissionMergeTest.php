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

namespace Tests\Feature_v2\Album;

use App\Models\AccessPermission;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Regression test for the bug report: a user who belongs to two groups
 * sharing the same album (e.g. "All" = View only, "Support_VIP" = View,
 * Access, Download) only received the grants of whichever group's
 * AccessPermission row was created first, instead of the union of both.
 *
 * See Feature 048 (docs/specs/4-architecture/features/048-fix-multi-group-permissions).
 */
class MultiGroupPermissionMergeTest extends BaseApiWithDataTest
{
	public function testUserInTwoGroupsReceivesUnionOfGrantsLowGroupFirst(): void
	{
		$this->userWithGroup1->user_groups()->attach($this->group2);

		// "All" - View only, created first.
		AccessPermission::factory()
			->for_user_group($this->group1)
			->for_album($this->album3)
			->visible()
			->create();

		// "Support_VIP" - View, Access, Download, created second.
		AccessPermission::factory()
			->for_user_group($this->group2)
			->for_album($this->album3)
			->visible()
			->grants_download()
			->grants_full_photo()
			->create();

		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album3->id]);
		$this->assertOk($response);
		$response->assertJsonPath('resource.rights.can_download', true);
	}

	public function testUserInTwoGroupsReceivesUnionOfGrantsHighGroupFirst(): void
	{
		$this->userWithGroup1->user_groups()->attach($this->group2);

		// "Support_VIP" - View, Access, Download, created first this time.
		AccessPermission::factory()
			->for_user_group($this->group2)
			->for_album($this->album3)
			->visible()
			->grants_download()
			->grants_full_photo()
			->create();

		// "All" - View only, created second.
		AccessPermission::factory()
			->for_user_group($this->group1)
			->for_album($this->album3)
			->visible()
			->create();

		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album3->id]);
		$this->assertOk($response);
		$response->assertJsonPath('resource.rights.can_download', true);
	}

	/**
	 * NFR-048-01: merging a second matching group's AccessPermission row must
	 * not add any SQL query — access_permissions and user_groups are already
	 * eager-loaded/read by the pre-fix code, so the merge is purely in-memory.
	 *
	 * A warm-up call primes process-lifetime caches (e.g. ConfigManager, which
	 * only issues its "configs" table queries once per test method regardless
	 * of scenario) so the two measured calls below are compared on equal
	 * footing instead of the first call absorbing one-time warm-up queries.
	 */
	public function testQueryCountUnaffectedByNumberOfMatchingGroups(): void
	{
		AccessPermission::factory()
			->for_user_group($this->group1)
			->for_album($this->album3)
			->visible()
			->grants_download()
			->create();

		$this->assertOk($this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album3->id]));

		// Baseline: the user matches a single group's AccessPermission row.
		DB::enableQueryLog();
		$baseline_response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album3->id]);
		$this->assertOk($baseline_response);
		$baseline_count = count(DB::getQueryLog());
		DB::flushQueryLog();
		DB::disableQueryLog();

		// Add a second group + matching row for the SAME user on the SAME album.
		$this->userWithGroup1->user_groups()->attach($this->group2);
		AccessPermission::factory()
			->for_user_group($this->group2)
			->for_album($this->album3)
			->visible()
			->create();

		DB::enableQueryLog();
		$multi_group_response = $this->actingAs($this->userWithGroup1)->getJsonWithData('Album::head', ['album_id' => $this->album3->id]);
		$this->assertOk($multi_group_response);
		$multi_group_count = count(DB::getQueryLog());
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertSame(
			$baseline_count,
			$multi_group_count,
			'Adding a second matching group permission must not add any SQL query (NFR-048-01).'
		);
	}
}
