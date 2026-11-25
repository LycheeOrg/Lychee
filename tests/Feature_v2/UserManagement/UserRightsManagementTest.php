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

namespace Tests\Feature_v2\UserManagement;

use App\Models\Configs;
use App\Models\User;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: admins set the share and edit rights; upload implies both;
 * registration reads the new-account settings
 * (FR-118-17, FR-118-19, FR-118-22, S-118-12, S-118-13, S-118-20).
 */
class UserRightsManagementTest extends BaseApiWithDataTest
{
	public function tearDown(): void
	{
		Configs::set('grant_new_user_upload_rights', '0');
		Configs::set('grant_new_user_share_rights', '0');
		Configs::set('grant_new_user_edit_rights', '0');
		Configs::set('user_registration_enabled', '0');
		parent::tearDown();
	}

	/**
	 * @return array<string,mixed>
	 */
	private function createPayload(string $username, bool $upload, bool $share, bool $edit): array
	{
		return [
			'username' => $username,
			'password' => 'password',
			'may_upload' => $upload,
			'may_share' => $share,
			'may_edit' => $edit,
			'may_edit_own_settings' => true,
		];
	}

	public function testCreateWithShareOnly(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement', $this->createPayload('sharer', false, true, false));
		$this->assertCreated($response);
		$response->assertJson(['may_upload' => false, 'may_share' => true, 'may_edit' => false]);

		$user = User::query()->where('username', '=', 'sharer')->firstOrFail();
		self::assertFalse($user->may_upload);
		self::assertTrue($user->may_share);
		self::assertFalse($user->may_edit);
	}

	public function testCreateWithUploadGetsAllThree(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement', $this->createPayload('uploader', true, false, false));
		$this->assertCreated($response);
		$response->assertJson(['may_upload' => true, 'may_share' => true, 'may_edit' => true]);
	}

	public function testCreateWithoutTheNewRightsIsRefused(): void
	{
		$payload = $this->createPayload('incomplete', false, false, false);
		unset($payload['may_share'], $payload['may_edit']);

		$response = $this->actingAs($this->admin)->postJson('UserManagement', $payload);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['may_share', 'may_edit']);
	}

	public function testEditSetsTheNewRights(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('UserManagement', [
			'id' => $this->userNoUpload->id,
			'username' => $this->userNoUpload->username,
			'may_upload' => false,
			'may_share' => false,
			'may_edit' => true,
			'may_edit_own_settings' => true,
		]);
		$this->assertNoContent($response);

		$user = $this->userNoUpload->fresh();
		self::assertFalse($user->may_share);
		self::assertTrue($user->may_edit);

		$response = $this->actingAs($this->admin)->patchJson('UserManagement', [
			'id' => $this->userNoUpload->id,
			'username' => $this->userNoUpload->username,
			'may_upload' => true,
			'may_share' => false,
			'may_edit' => false,
			'may_edit_own_settings' => true,
		]);
		$this->assertNoContent($response);

		$user = $this->userNoUpload->fresh();
		self::assertTrue($user->may_share);
		self::assertTrue($user->may_edit);
	}

	public function testListShowsTheNewRights(): void
	{
		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$users = collect($response->json())->keyBy('id');
		self::assertTrue($users[$this->userMayUpload1->id]['may_share']);
		self::assertTrue($users[$this->userMayUpload1->id]['may_edit']);
		self::assertFalse($users[$this->userNoUpload->id]['may_share']);
		self::assertFalse($users[$this->userNoUpload->id]['may_edit']);
	}

	public function testRegistrationAppliesTheNewAccountSettings(): void
	{
		Configs::set('user_registration_enabled', '1');
		Configs::set('grant_new_user_upload_rights', '0');
		Configs::set('grant_new_user_share_rights', '1');
		Configs::set('grant_new_user_edit_rights', '0');

		$response = $this->putJson('/Profile', [
			'username' => 'registered',
			'email' => 'registered@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);
		$this->assertCreated($response);

		$user = User::query()->where('username', '=', 'registered')->firstOrFail();
		self::assertFalse($user->may_upload);
		self::assertTrue($user->may_share);
		self::assertFalse($user->may_edit);
	}
}
