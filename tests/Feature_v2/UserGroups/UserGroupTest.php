<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Feature_v2\UserGroups;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UserGroupTest extends BaseApiWithDataTest
{
	/**
	 * Test Create Groups.
	 */
	public function testCreateGroupUnauthorized(): void
	{
		$response = $this->postJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('/UserGroups', ['name' => 'Test Group']);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroup1)->postJson('/UserGroups', ['name' => 'Test Group']);
		$this->assertForbidden($response);
	}

	public function testCreateGroupAuthorized(): void
	{
		$response = $this->actingAs($this->admin)->postJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('/UserGroups', ['name' => 'Test Group']);
		$this->assertCreated($response);
		self::assertEquals('Test Group', $response->json('name'));
	}

	/**
	 * Test Update Groups.
	 */
	public function testUpdateGroupUnauthorized(): void
	{
		$response = $this->patchJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('/UserGroups', ['group_id' => $this->group1->id, 'name' => 'Updated Name']);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroup1)->patchJson('/UserGroups', ['group_id' => $this->group1->id, 'name' => 'Updated Name']);
		$this->assertForbidden($response);
	}

	public function testUpdateGroupAuthorized(): void
	{
		$response = $this->actingAs($this->userWithGroupAdmin)->patchJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->patchJson('/UserGroups', ['group_id' => $this->group1->id, 'name' => $this->group2->name]);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->patchJson('/UserGroups', ['group_id' => $this->group1->id, 'name' => 'Updated Name']);
		$this->assertOk($response);
		self::assertEquals('Updated Name', $response->json('name'));
	}

	/**
	 * Test Delete Groups.
	 */
	public function testDeleteGroupUnauthorized(): void
	{
		$response = $this->deleteJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('/UserGroups', ['group_id' => $this->group1->id]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->deleteJson('/UserGroups', ['group_id' => $this->group1->id]);
		$this->assertForbidden($response);
	}

	public function testDeleteGroupAuthorized(): void
	{
		$response = $this->actingAs($this->admin)->deleteJson('/UserGroups', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->deleteJson('/UserGroups', ['group_id' => $this->group1->id]);
		$this->assertNoContent($response);
	}

	/**
	 * Test List Groups.
	 */
	public function testListGroupsUnauthorized(): void
	{
		$response = $this->getJson('/UserGroups');
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->getJson('/UserGroups');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userWithGroup1)->getJson('/UserGroups');
		$this->assertForbidden($response);
	}

	public function testListGroupsWithGroup(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('/UserGroups');
		$this->assertOk($response);
		$response->assertJson([
			'user_groups' => [
				['id' => $this->group1->id, 'name' => $this->group1->name, 'rights' => ['can_edit' => false, 'can_manage' => false]],
				['id' => $this->group2->id, 'name' => $this->group2->name, 'rights' => ['can_edit' => false, 'can_manage' => false]],
			],
			'can_create_delete_user_groups' => false,
		]);

		$response = $this->actingAs($this->userWithGroupAdmin)->getJson('/UserGroups');
		$this->assertOk($response);
		$response->assertJson([
			'user_groups' => [
				['id' => $this->group1->id,
					'name' => $this->group1->name,
					'members' => [
						['id' => $this->userWithGroupAdmin->id, 'username' => $this->userWithGroupAdmin->username, 'role' => 'admin'],
						['id' => $this->userWithGroup1->id, 'username' => $this->userWithGroup1->username, 'role' => 'member'],
					],
					'rights' => [
						'can_edit' => true,
						'can_manage' => true,
					],
				],
				['id' => $this->group2->id,
					'name' => $this->group2->name,
					'rights' => [
						'can_edit' => false,
						'can_manage' => false,
					],
				],
			],
			'can_create_delete_user_groups' => false,
		]);

		$response = $this->actingAs($this->admin)->getJson('/UserGroups');
		$this->assertOk($response);
		$response->assertJson([
			'user_groups' => [
				['id' => $this->group1->id, 'name' => $this->group1->name, 'rights' => ['can_edit' => true, 'can_manage' => true]],
				['id' => $this->group2->id, 'name' => $this->group2->name, 'rights' => ['can_edit' => true, 'can_manage' => true]],
			],
			'can_create_delete_user_groups' => true,
		]);
	}
}
