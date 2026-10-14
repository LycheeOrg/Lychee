<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\AssistedVision\FacePermissions;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Global right `modules.can_quick_name_faces` (Feature 093, FR-093-07).
 */
class FaceQuickNamingRightsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
	}

	private function canQuickName(?\App\Models\User $user): bool
	{
		$response = $user === null ? $this->getJson('Auth::rights') : $this->actingAs($user)->getJson('Auth::rights');
		$this->assertOk($response);

		return $response->json('modules.can_quick_name_faces');
	}

	public function testGuestCannot(): void
	{
		self::assertFalse($this->canQuickName(null));
	}

	public function testUserCanInPublicAndPrivateMode(): void
	{
		self::assertTrue($this->canQuickName($this->userMayUpload1));
		Configs::set('ai_vision_face_permission_mode', 'private');
		self::assertTrue($this->canQuickName($this->userMayUpload1));
	}

	public function testUserCannotInPrivacyPreservingOrRestrictedMode(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		self::assertFalse($this->canQuickName($this->userMayUpload1));
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		self::assertFalse($this->canQuickName($this->userMayUpload1));
	}

	public function testAdminCanInEveryMode(): void
	{
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			self::assertTrue($this->canQuickName($this->admin), $mode);
		}
	}

	public function testFalseWhenStructOfArrayFlagIsOff(): void
	{
		config(['features.struct-of-array' => false]);
		self::assertFalse($this->canQuickName($this->admin));
	}

	public function testFalseWhenFaceRecognitionIsOff(): void
	{
		Configs::set('ai_vision_face_enabled', '0');
		self::assertFalse($this->canQuickName($this->admin));
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_enabled', '0');
		self::assertFalse($this->canQuickName($this->admin));
	}
}
