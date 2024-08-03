<?php

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

class ListUserTest extends BaseApiV2Test
{
	public function testListUsersGuest(): void
	{
		$response = $this->getJson('Users');
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Users');
		$this->assertForbidden($response);
	}

	public function testListUsersAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Users');
		$this->assertOk($response);
		$response->assertJson(
			['users' => [
				[
					'id' => $this->userMayUpload1->id,
					'username' => $this->userMayUpload1->username,
					'may_administrate' => $this->userMayUpload1->may_administrate,
					'may_upload' => $this->userMayUpload1->may_upload,
					'may_edit_own_settings' => $this->userMayUpload1->may_edit_own_settings,
				],
				[
					'id' => $this->userMayUpload2->id,
					'username' => $this->userMayUpload2->username,
					'may_administrate' => $this->userMayUpload2->may_administrate,
					'may_upload' => $this->userMayUpload2->may_upload,
					'may_edit_own_settings' => $this->userMayUpload2->may_edit_own_settings,
				],
				[
					'id' => $this->userNoUpload->id,
					'username' => $this->userNoUpload->username,
					'may_administrate' => $this->userNoUpload->may_administrate,
					'may_upload' => $this->userNoUpload->may_upload,
					'may_edit_own_settings' => $this->userNoUpload->may_edit_own_settings,
				],
				[
					'id' => $this->userLocked->id,
					'username' => $this->userLocked->username,
					'may_administrate' => $this->userLocked->may_administrate,
					'may_upload' => $this->userLocked->may_upload,
					'may_edit_own_settings' => $this->userLocked->may_edit_own_settings,
				],
			]]);
		$response->assertDontSee((string) $this->admin->id);
		$response->assertDontSee($this->admin->username);
	}
}