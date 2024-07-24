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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class ProfileTest extends BaseApiV2Test
{
	// Route::post('/Profile::updateLogin', [ProfileController::class, 'updateLogin']);
	// Route::post('/Profile::setEmail', [ProfileController::class, 'setEmail']);
	// Route::post('/Profile::resetToken', [ProfileController::class, 'resetToken']);
	// Route::post('/Profile::unsetToken', [ProfileController::class, 'unsetToken']);

	public function testUpdateLoginGuest(): void
	{
		$response = $this->postJson('Profile::updateLogin', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::updateLogin', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirm' => 'password2',
		]);
		$this->assertUnauthorized($response);
	}

	public function testSetEmailGuest(): void
	{
		$response = $this->postJson('Profile::setEmail', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::setEmail', [
			'email' => 'something@something.com',
		]);
		$this->assertUnauthorized($response);
	}

	public function testUnsetResetTokenGuest(): void
	{
		$response = $this->postJson('Profile::resetToken', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Profile::unsetToken', []);
		$this->assertUnauthorized($response);
	}

	public function testUserLocked(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('Profile::updateLogin', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirm' => 'password2',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::setEmail', [
			'email' => 'something@something.com',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::resetToken', []);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::unsetToken', []);
		$this->assertForbidden($response);
	}

	public function testUserUnlocked(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateLogin', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirm' => 'password2',
		]);
		$this->assertCreated($response);

		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		$response = $this->postJson('Auth::login', [
			'username' => 'username',
			'password' => 'password2',
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::setEmail', [
			'email' => 'something@something.com',
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::resetToken', []);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::unsetToken', []);
		$this->assertNoContent($response);
	}
}