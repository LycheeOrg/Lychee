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

use Tests\Feature_v2\Base\BaseApiV2Test;

class EditUserTest extends BaseApiV2Test
{
	public function testEditUserGuest(): void
	{
		$response = $this->postJson('UserManagement::save');
		$this->assertUnprocessable($response);

		$response = $this->postJson('UserManagement::save', [
			'id' => $this->userMayUpload1->id,
			'username' => $this->userMayUpload1->username,
			'may_upload' => $this->userMayUpload1->may_upload,
			'may_edit_own_settings' => $this->userMayUpload1->may_edit_own_settings,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('UserManagement::save', [
			'id' => $this->userMayUpload1->id,
			'username' => $this->userMayUpload1->username,
			'may_upload' => $this->userMayUpload1->may_upload,
			'may_edit_own_settings' => $this->userMayUpload1->may_edit_own_settings,
		]);
		$this->assertForbidden($response);
	}

	public function testEditUserAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement::save', [
			'id' => $this->userMayUpload1->id,
			'username' => 'anotherUsername',
			'may_upload' => false,
			'may_edit_own_settings' => false,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$response->assertJsonFragment(
			[
				'id' => $this->userMayUpload1->id,
				'username' => 'anotherUsername',
				'may_administrate' => false,
				'may_upload' => false,
				'may_edit_own_settings' => false,
			],
		);
	}
}