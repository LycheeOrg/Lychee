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

namespace Tests\Feature_v2\Album;

use App\Enum\SharedAlbumsVisibility;
use App\Enum\UserSharedAlbumsVisibility;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SharedAlbumsVisibilityTest extends BaseApiWithDataTest
{
	public function testGuestDoesNotReceiveSharedAlbumsVisibilityMode(): void
	{
		$response = $this->getJson('Albums');
		$this->assertOk($response);

		// Guest should not have shared_albums_visibility_mode set
		$this->assertNull($response->json('config.shared_albums_visibility_mode'));
	}

	public function testUserReceivesDefaultSharedAlbumsVisibilityMode(): void
	{
		// Set server default to 'show'
		Configs::set('shared_albums_visibility_default', 'show');

		// Refresh user to ensure all attributes are loaded (including shared_albums_visibility)
		$this->userMayUpload1->refresh();

		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);

		// User with DEFAULT preference should receive server default
		$this->assertEquals('show', $response->json('config.shared_albums_visibility_mode'));
	}

	public function testUserReceivesServerDefaultWhenPreferenceIsDefault(): void
	{
		// Set server default to 'separate'
		Configs::set('shared_albums_visibility_default', 'separate');

		// Refresh user to ensure all attributes are loaded (including shared_albums_visibility)
		$this->userMayUpload1->refresh();

		// User preference is DEFAULT by default (from migration)
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);

		$this->assertEquals('separate', $response->json('config.shared_albums_visibility_mode'));
	}

	public function testUserReceivesOwnPreferenceWhenSet(): void
	{
		// Set server default to 'show'
		Configs::set('shared_albums_visibility_default', 'show');

		// Set user preference to 'hide'
		$this->userMayUpload1->shared_albums_visibility = UserSharedAlbumsVisibility::HIDE;
		$this->userMayUpload1->save();

		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);

		// User should receive their own preference, not server default
		$this->assertEquals('hide', $response->json('config.shared_albums_visibility_mode'));
	}

	public function testAllSharedAlbumsVisibilityModes(): void
	{
		// Refresh user to ensure all attributes are loaded (including shared_albums_visibility)
		$this->userMayUpload1->refresh();

		$modes = [
			SharedAlbumsVisibility::SHOW,
			SharedAlbumsVisibility::SEPARATE,
			SharedAlbumsVisibility::SEPARATE_SHARED_ONLY,
			SharedAlbumsVisibility::HIDE,
		];

		foreach ($modes as $mode) {
			Configs::set('shared_albums_visibility_default', $mode->value);

			$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
			$this->assertOk($response);

			$this->assertEquals($mode->value, $response->json('config.shared_albums_visibility_mode'));
		}
	}

	public function testUpdateSharedAlbumsVisibilityPreference(): void
	{
		// Update user preference via profile endpoint
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateSharedAlbumsVisibility', [
			'shared_albums_visibility' => 'separate',
		]);
		$this->assertCreated($response);

		// Reload user and verify preference was saved
		$this->userMayUpload1->refresh();
		$this->assertEquals(UserSharedAlbumsVisibility::SEPARATE, $this->userMayUpload1->shared_albums_visibility);

		// Verify the mode is returned correctly in Albums response
		Configs::set('shared_albums_visibility_default', 'show');
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$this->assertEquals('separate', $response->json('config.shared_albums_visibility_mode'));
	}

	public function testUpdateSharedAlbumsVisibilityToDefault(): void
	{
		// First set a non-default preference
		$this->userMayUpload1->shared_albums_visibility = UserSharedAlbumsVisibility::HIDE;
		$this->userMayUpload1->save();

		// Update user preference back to default
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateSharedAlbumsVisibility', [
			'shared_albums_visibility' => 'default',
		]);
		$this->assertCreated($response);

		// Reload user and verify preference was saved
		$this->userMayUpload1->refresh();
		$this->assertEquals(UserSharedAlbumsVisibility::DEFAULT, $this->userMayUpload1->shared_albums_visibility);

		// Verify server default is now used
		Configs::set('shared_albums_visibility_default', 'show');
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$this->assertEquals('show', $response->json('config.shared_albums_visibility_mode'));
	}

	public function testInvalidSharedAlbumsVisibilityRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateSharedAlbumsVisibility', [
			'shared_albums_visibility' => 'invalid_value',
		]);
		$this->assertUnprocessable($response);
	}

	public function testLockedUserCannotUpdateSharedAlbumsVisibility(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('Profile::updateSharedAlbumsVisibility', [
			'shared_albums_visibility' => 'hide',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateSharedAlbumsVisibilityRequiresAuthentication(): void
	{
		$response = $this->postJson('Profile::updateSharedAlbumsVisibility', [
			'shared_albums_visibility' => 'hide',
		]);
		$this->assertUnauthorized($response);
	}

	public function testUpdateSharedAlbumsVisibilityRequiresField(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateSharedAlbumsVisibility', []);
		$this->assertUnprocessable($response);
	}

	public function testUpdateSharedAlbumsVisibilityAllModes(): void
	{
		$modes = [
			UserSharedAlbumsVisibility::DEFAULT,
			UserSharedAlbumsVisibility::SHOW,
			UserSharedAlbumsVisibility::SEPARATE,
			UserSharedAlbumsVisibility::SEPARATE_SHARED_ONLY,
			UserSharedAlbumsVisibility::HIDE,
		];

		foreach ($modes as $mode) {
			$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::updateSharedAlbumsVisibility', [
				'shared_albums_visibility' => $mode->value,
			]);
			$this->assertCreated($response);

			$this->userMayUpload1->refresh();
			$this->assertEquals($mode, $this->userMayUpload1->shared_albums_visibility);
		}
	}
}
