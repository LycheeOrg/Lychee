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

namespace Tests\Feature_v2\TrustLevel;

use App\Enum\UserUploadTrustLevel;
use App\Models\User;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for upload_trust_level on the UserManagement API.
 */
class UserTrustLevelTest extends BaseApiWithDataTest
{
	public function testListUsersIncludesTrustLevel(): void
	{
		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$response->assertJsonStructure([
			'*' => ['id', 'username', 'upload_trust_level'],
		]);
	}

	public function testCreateUserWithExplicitTrustLevel(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement', [
			'username' => 'trust_test_user_check',
			'password' => 'Pa$$w0rd123!',
			'may_upload' => true,
			'may_edit_own_settings' => true,
			'upload_trust_level' => 'check',
		]);
		$this->assertCreated($response);
		$response->assertJsonPath('upload_trust_level', 'check');

		// Cleanup
		User::where('username', 'trust_test_user_check')->delete();
	}

	public function testCreateUserDefaultsTrustLevelFromConfig(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement', [
			'username' => 'trust_test_user_default',
			'password' => 'Pa$$w0rd123!',
			'may_upload' => true,
			'may_edit_own_settings' => true,
		]);
		$this->assertCreated($response);
		// The default is 'trusted' per the config migration default
		$response->assertJsonPath('upload_trust_level', 'trusted');

		// Cleanup
		User::where('username', 'trust_test_user_default')->delete();
	}

	public function testUpdateUserTrustLevel(): void
	{
		// userMayUpload1 is trusted by default
		$this->assertEquals(UserUploadTrustLevel::TRUSTED, $this->userMayUpload1->upload_trust_level);

		$response = $this->actingAs($this->admin)->patchJson('UserManagement', [
			'id' => $this->userMayUpload1->id,
			'username' => $this->userMayUpload1->username,
			'may_upload' => true,
			'may_edit_own_settings' => true,
			'upload_trust_level' => 'check',
		]);
		$this->assertNoContent($response);

		$this->userMayUpload1->refresh();
		$this->assertEquals(UserUploadTrustLevel::CHECK, $this->userMayUpload1->upload_trust_level);

		// Restore to trusted
		$this->userMayUpload1->upload_trust_level = UserUploadTrustLevel::TRUSTED;
		$this->userMayUpload1->save();
	}

	public function testUpdateUserWithoutTrustLevelPreservesExisting(): void
	{
		// Set to 'check' first
		$this->userMayUpload1->upload_trust_level = UserUploadTrustLevel::CHECK;
		$this->userMayUpload1->save();

		$response = $this->actingAs($this->admin)->patchJson('UserManagement', [
			'id' => $this->userMayUpload1->id,
			'username' => $this->userMayUpload1->username,
			'may_upload' => true,
			'may_edit_own_settings' => true,
			// No upload_trust_level field → should preserve existing
		]);
		$this->assertNoContent($response);

		$this->userMayUpload1->refresh();
		$this->assertEquals(UserUploadTrustLevel::CHECK, $this->userMayUpload1->upload_trust_level);

		// Restore
		$this->userMayUpload1->upload_trust_level = UserUploadTrustLevel::TRUSTED;
		$this->userMayUpload1->save();
	}

	public function testNonAdminCannotCreateUsers(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('UserManagement', [
			'username' => 'should_not_create',
			'password' => 'Pa$$w0rd123!',
			'may_upload' => false,
			'may_edit_own_settings' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testUnauthenticatedCannotCreateUsers(): void
	{
		$response = $this->postJson('UserManagement', [
			'username' => 'should_not_create',
			'password' => 'Pa$$w0rd123!',
			'may_upload' => false,
			'may_edit_own_settings' => true,
		]);
		$this->assertUnauthorized($response);
	}
}
