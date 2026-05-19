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

namespace Tests\Feature_v2\AiVision;

use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for PhotoPolicy face gate constants across all four FacePermissionMode values.
 *
 * Actor roles:
 *   - photo owner  \u2192 userMayUpload1 (owns photo1 in album1)
 *   - logged non-owner \u2192 userMayUpload2
 *   - guest \u2192 unauthenticated
 *   - admin \u2192 admin
 */
class PhotoPolicyFaceTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// \u2500\u2500 canViewFaceOverlays \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanViewFaceOverlaysPublicModeGuestCannotSeePrivatePhoto(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		// album1 is private; guest cannot see photo1
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPublicModeOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPublicModeLoggedNonOwnerWithAlbumAccess(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		// userMayUpload2 has perm1 on album1 -> can access -> can view overlays.
		// Use a fresh model so the album\'s cached access_permissions include perm1.
		$this->actingAs($this->userMayUpload2);
		$fresh_photo = Photo::find($this->photo1->id);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $fresh_photo));
	}

	public function testCanViewFaceOverlaysPrivateModeLoggedOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPrivateModeLoggedNonOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPrivateModeGuestCannotView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPrivacyPreservingOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPrivacyPreservingNonOwnerDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysPrivacyPreservingGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysRestrictedOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysRestrictedNonOwnerDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testCanViewFaceOverlaysAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1), "Admin failed for mode: {$mode}");
		}
	}

	public function testCanViewFaceOverlaysAiVisionDisabledDeniesNonAdmin(): void
	{
		Configs::set('ai_vision_enabled', '0');
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	// \u2500\u2500 canDismissFace \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanDismissFaceOwnerCanDismissInAllModes(): void
	{
		$this->actingAs($this->userMayUpload1);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1), "Owner failed for mode: {$mode}");
		}
	}

	public function testCanDismissFaceNonOwnerDeniedInAllModes(): void
	{
		$this->actingAs($this->userMayUpload2);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1), "Non-owner passed for mode: {$mode}");
		}
	}

	public function testCanDismissFaceGuestDenied(): void
	{
		Auth::logout();
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testCanDismissFaceAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testCanDismissFaceAiVisionDisabledDeniesNonAdmin(): void
	{
		Configs::set('ai_vision_enabled', '0');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	// \u2500\u2500 canAssignFaceOnPhoto \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanAssignFaceOnPhotoPublicModeOwnerCanAssign(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testCanAssignFaceOnPhotoPublicModeNonOwnerLoggedInCanAssign(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testCanAssignFaceOnPhotoPublicModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testCanAssignFaceOnPhotoPrivacyPreservingOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testCanAssignFaceOnPhotoRestrictedDeniesEveryone(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testCanAssignFaceOnPhotoAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1), "Admin failed for mode: {$mode}");
		}
	}

	// \u2500\u2500 canTriggerScanOnPhoto \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanTriggerScanOnPhotoPublicAndPrivateModeLoggedUser(): void
	{
		foreach (['public', 'private'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->actingAs($this->userMayUpload1);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1), "Owner failed for mode: {$mode}");
			$this->actingAs($this->userMayUpload2);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1), "Non-owner failed for mode: {$mode}");
		}
	}

	public function testCanTriggerScanOnPhotoPublicModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testCanTriggerScanOnPhotoPrivacyPreservingOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testCanTriggerScanOnPhotoRestrictedOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testCanTriggerScanOnPhotoAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1), "Admin failed for mode: {$mode}");
		}
	}

	public function testCanTriggerScanOnPhotoAiVisionDisabledDeniesNonAdmin(): void
	{
		Configs::set('ai_vision_enabled', '0');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}
}
