<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Feature_v2\UserGroups;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UserGroupMembershipTest extends BaseApiWithDataTest
{
	public function testReadGroupUnauthorized(): void
	{
		$response = $this->getJsonWithData('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('/UserGroups/Users', [
			'group_id' => $this->group1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('/UserGroups/Users', [
			'group_id' => $this->group1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testReadGroupAuthorized(): void
	{
		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroup1)->getJsonWithData('/UserGroups/Users', [
			'group_id' => $this->group1->id,
		]);
		$this->assertOk($response);
	}

	public function testAddUserToGroupUnauthorized(): void
	{
		$response = $this->postJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroup1)->postJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testAddUserToGroupAuthorized(): void
	{
		$response = $this->actingAs($this->userWithGroupAdmin)->postJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->postJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertCreated($response);
	}

	public function testRemoveUserFromGroupUnauthorized(): void
	{
		$response = $this->deleteJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroup1)->deleteJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testRemoveUserFromGroupAuthorized(): void
	{
		$response = $this->actingAs($this->userWithGroupAdmin)->deleteJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->deleteJson('/UserGroups/Users', [
			'user_id' => $this->userWithGroup1->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertOk($response);
	}

	public function testUpdateUserRoleUnauthorized(): void
	{
		$response = $this->patchJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
			'role' => 'admin',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userWithGroup1)->patchJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
			'role' => 'admin',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateUserRoleAuthorized(): void
	{
		$response = $this->actingAs($this->userWithGroupAdmin)->patchJson('/UserGroups/Users', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userWithGroupAdmin)->patchJson('/UserGroups/Users', [
			'user_id' => $this->userWithGroup1->id,
			'group_id' => $this->group1->id,
			'role' => 'admin',
		]);
		$this->assertOk($response);
		$response->assertJsonPath('members.0.role', 'admin');
		$response->assertJsonPath('members.1.role', 'admin');
	}
}
