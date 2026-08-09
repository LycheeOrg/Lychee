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

namespace Tests\Feature_v2\UserGroup;

use App\Events\UserGroupMembershipChanged;
use Illuminate\Support\Facades\Event;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UserGroupsManagementTest extends BaseApiWithDataTest
{
	public function testAddUserDispatchesUserGroupMembershipChanged(): void
	{
		Event::fake([UserGroupMembershipChanged::class]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->postJson('UserGroups/Users', [
			'group_id' => $this->group2->id,
			'user_id' => $this->userMayUpload1->id,
			'role' => 'member',
		]);
		$this->assertCreated($response);

		Event::assertDispatched(UserGroupMembershipChanged::class, fn (UserGroupMembershipChanged $e) => $e->user_id === $this->userMayUpload1->id);
	}

	public function testRemoveUserDispatchesUserGroupMembershipChanged(): void
	{
		Event::fake([UserGroupMembershipChanged::class]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->deleteJson('UserGroups/Users', [
			'group_id' => $this->group1->id,
			'user_id' => $this->userWithGroup1->id,
		]);
		$this->assertOk($response);

		Event::assertDispatched(UserGroupMembershipChanged::class, fn (UserGroupMembershipChanged $e) => $e->user_id === $this->userWithGroup1->id);
	}

	public function testUpdateUserRoleDispatchesUserGroupMembershipChanged(): void
	{
		Event::fake([UserGroupMembershipChanged::class]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->patchJson('UserGroups/Users', [
			'group_id' => $this->group1->id,
			'user_id' => $this->userWithGroup1->id,
			'role' => 'admin',
		]);
		$this->assertOk($response);

		Event::assertDispatched(UserGroupMembershipChanged::class, fn (UserGroupMembershipChanged $e) => $e->user_id === $this->userWithGroup1->id);
	}
}
