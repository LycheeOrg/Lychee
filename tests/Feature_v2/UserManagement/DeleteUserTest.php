<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\UserManagement;

use App\Models\User;
use Tests\Feature_v2\Base\BaseApiV2Test;

class DeleteUserTest extends BaseApiV2Test
{
	public function testDeleteUserGuest(): void
	{
		$response = $this->postJson('UserManagement::delete');
		$this->assertUnprocessable($response);

		$response = $this->postJson('UserManagement::delete', [
			'id' => $this->userMayUpload1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('UserManagement::delete', [
			'id' => $this->userMayUpload1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteUserAdmin(): void
	{
		$num_users = User::count();
		$response = $this->actingAs($this->admin)->postJson('UserManagement::delete', [
			'id' => $this->userNoUpload->id,
		]);
		$this->assertNoContent($response);
		self::assertEquals($num_users - 1, User::count());

		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$response->assertDontSee($this->userNoUpload->username);
	}
}