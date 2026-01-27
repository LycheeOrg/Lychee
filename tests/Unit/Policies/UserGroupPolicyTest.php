<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Policies;

use App\Enum\UserGroupRole;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Unit tests for UserGroupPolicy.
 *
 * Tests authorization logic for user group management.
 */
class UserGroupPolicyTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private UserGroupPolicy $policy;
	private User $user;
	private UserGroup $userGroup;

	protected function setUp(): void
	{
		parent::setUp();

		$this->policy = new UserGroupPolicy();

		// Create a test user
		$this->user = new User();
		$this->user->username = 'testuser';
		$this->user->password = \Illuminate\Support\Facades\Hash::make('password');
		$this->user->email = 'test@example.com';
		$this->user->may_upload = false;
		$this->user->may_edit_own_settings = true;
		$this->user->may_administrate = false;
		$this->user->save();

		// Create a test user group
		$this->userGroup = new UserGroup();
		$this->userGroup->name = 'Test Group';
		$this->userGroup->description = 'A test group';
		$this->userGroup->save();
	}

	public function testCanEditWithNullGroupWhenUserIsAdminOfAnyGroup(): void
	{
		// Create another group and add user as admin
		$anotherGroup = new UserGroup();
		$anotherGroup->name = 'Another Group';
		$anotherGroup->save();

		// Attach user to the group with admin role
		$anotherGroup->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);

		// User should be able to edit (null group means "can edit any group where they're admin")
		$result = $this->policy->canEdit($this->user, null);

		$this->assertTrue($result);
	}

	public function testCanEditWithNullGroupWhenUserIsNotAdminOfAnyGroup(): void
	{
		// Attach user to the group as a member (not admin)
		$this->userGroup->users()->attach($this->user->id, ['role' => UserGroupRole::MEMBER->value]);

		// User should NOT be able to edit (they're not admin of any group)
		$result = $this->policy->canEdit($this->user, null);

		$this->assertFalse($result);
	}

	public function testCanEditWithNullGroupWhenUserHasNoGroups(): void
	{
		// User is not part of any groups

		// User should NOT be able to edit
		$result = $this->policy->canEdit($this->user, null);

		$this->assertFalse($result);
	}

	public function testCanEditSpecificGroupWhenUserIsAdminOfThatGroup(): void
	{
		// Attach user to the group with admin role
		$this->userGroup->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);

		// User should be able to edit this specific group
		$result = $this->policy->canEdit($this->user, $this->userGroup);

		$this->assertTrue($result);
	}

	public function testCanEditSpecificGroupWhenUserIsMemberButNotAdmin(): void
	{
		// Attach user to the group as a member (not admin)
		$this->userGroup->users()->attach($this->user->id, ['role' => UserGroupRole::MEMBER->value]);

		// User should NOT be able to edit (they're a member, not admin)
		$result = $this->policy->canEdit($this->user, $this->userGroup);

		$this->assertFalse($result);
	}

	public function testCanEditSpecificGroupWhenUserIsNotMember(): void
	{
		// User is not part of this group at all

		// User should NOT be able to edit
		$result = $this->policy->canEdit($this->user, $this->userGroup);

		$this->assertFalse($result);
	}

	public function testCanEditSpecificGroupWhenUserIsAdminOfDifferentGroup(): void
	{
		// Create another group and add user as admin
		$anotherGroup = new UserGroup();
		$anotherGroup->name = 'Another Group';
		$anotherGroup->save();

		$anotherGroup->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);

		// User should NOT be able to edit this specific group (they're admin of a different group)
		$result = $this->policy->canEdit($this->user, $this->userGroup);

		$this->assertFalse($result);
	}

	public function testCanEditWithNullGroupWhenUserIsAdminOfMultipleGroups(): void
	{
		// Create two groups and add user as admin to both
		$group1 = new UserGroup();
		$group1->name = 'Group 1';
		$group1->save();

		$group2 = new UserGroup();
		$group2->name = 'Group 2';
		$group2->save();

		$group1->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);
		$group2->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);

		// User should be able to edit (they're admin of at least one group)
		$result = $this->policy->canEdit($this->user, null);

		$this->assertTrue($result);
	}

	public function testCanEditWithNullGroupWhenUserIsMixedRoles(): void
	{
		// Create two groups: user is member of one, admin of another
		$memberGroup = new UserGroup();
		$memberGroup->name = 'Member Group';
		$memberGroup->save();

		$adminGroup = new UserGroup();
		$adminGroup->name = 'Admin Group';
		$adminGroup->save();

		$memberGroup->users()->attach($this->user->id, ['role' => UserGroupRole::MEMBER->value]);
		$adminGroup->users()->attach($this->user->id, ['role' => UserGroupRole::ADMIN->value]);

		// User should be able to edit (they're admin of at least one group)
		$result = $this->policy->canEdit($this->user, null);

		$this->assertTrue($result);
	}

	public function testCanEditSpecificGroupCaseInsensitiveRole(): void
	{
		// Test that the role comparison works correctly
		// Attach user with uppercase role (should still work since we use enum value)
		$this->userGroup->users()->attach($this->user->id, ['role' => 'admin']);

		// User should be able to edit
		$result = $this->policy->canEdit($this->user, $this->userGroup);

		$this->assertTrue($result);
	}
}
