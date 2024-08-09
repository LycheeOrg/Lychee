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

use App\Models\User;
use Tests\Feature_v2\Base\BaseApiV2Test;

class DeleteUserTest extends BaseApiV2Test
{
	public function testDeleteUserGuest(): void
	{
		$response = $this->postJson('Users::delete');
		$this->assertUnprocessable($response);

		$response = $this->postJson('Users::delete', [
			'id' => $this->userMayUpload1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Users::delete', [
			'id' => $this->userMayUpload1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteUserAdmin(): void
	{
		$num_users = User::count();
		$response = $this->actingAs($this->admin)->postJson('Users::delete', [
			'id' => $this->userNoUpload->id,
		]);
		$this->assertNoContent($response);
		$this->assertEquals($num_users - 1, User::count());

		$response = $this->actingAs($this->admin)->getJson('Users');
		$this->assertOk($response);
		$response->assertDontSee($this->userNoUpload->username);
	}
}