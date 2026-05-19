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

use App\Contracts\Models\AbstractAlbum;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for AlbumPolicy face gate constants across all four FacePermissionMode values.
 *
 * Actor roles:
 *   - album owner  \u2192 userMayUpload1 (owns album1)
 *   - logged non-owner \u2192 userMayUpload2
 *   - guest \u2192 unauthenticated
 *   - admin \u2192 admin
 */
class AlbumPolicyFaceTest extends BaseApiWithDataTest
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

	// \u2500\u2500 canViewAlbumPeople \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanViewAlbumPeoplePublicModeOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePublicModeNonOwnerWithAlbumAccessCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		// userMayUpload2 has perm1 on album1
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePublicModeGuestNoAccessDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		// album1 is not public -> guest cannot access
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePrivateModeOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePrivateModeNonOwnerCanView(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePrivateModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeoplePrivacyPreservingOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeopleRestrictedOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanViewAlbumPeopleAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]), "Admin failed for mode: {$mode}");
		}
	}

	public function testCanViewAlbumPeopleAiVisionDisabledDeniesNonAdmin(): void
	{
		Configs::set('ai_vision_enabled', '0');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	// \u2500\u2500 canTriggerScanOnAlbum \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanTriggerScanOnAlbumPublicModeOwnerCanTrigger(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanTriggerScanOnAlbumPublicModeNonOwnerLoggedCanTrigger(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanTriggerScanOnAlbumPublicModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanTriggerScanOnAlbumPrivacyPreservingAndRestrictedOwnerOnly(): void
	{
		foreach (['privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->actingAs($this->userMayUpload1);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]), "Owner failed for mode: {$mode}");
			$this->actingAs($this->userMayUpload2);
			$this->assertFalse(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]), "Non-owner passed for mode: {$mode}");
		}
	}

	public function testCanTriggerScanOnAlbumAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]), "Admin failed for mode: {$mode}");
		}
	}

	// \u2500\u2500 canAssignFaceInAlbum \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanAssignFaceInAlbumPublicAndPrivateModeLoggedUser(): void
	{
		foreach (['public', 'private'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->actingAs($this->userMayUpload1);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]), "Owner failed for mode: {$mode}");
			$this->actingAs($this->userMayUpload2);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]), "Non-owner failed for mode: {$mode}");
		}
	}

	public function testCanAssignFaceInAlbumPublicModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanAssignFaceInAlbumPrivacyPreservingOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanAssignFaceInAlbumRestrictedDeniesEveryone(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanAssignFaceInAlbumAdminAlwaysTrue(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]), "Admin failed for mode: {$mode}");
		}
	}

	// \u2500\u2500 canBatchFaceOps \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500

	public function testCanBatchFaceOpsPublicAndPrivateModeLoggedUser(): void
	{
		foreach (['public', 'private'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->actingAs($this->userMayUpload1);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]), "Owner failed for mode: {$mode}");
			$this->actingAs($this->userMayUpload2);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]), "Non-owner failed for mode: {$mode}");
		}
	}

	public function testCanBatchFaceOpsPublicModeGuestDenied(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'public');
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanBatchFaceOpsPrivacyPreservingOwnerOnly(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanBatchFaceOpsRestrictedDeniesEveryone(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testCanBatchFaceOpsNullAlbumDenies(): void
	{
		foreach (['public', 'private'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->actingAs($this->userMayUpload1);
			$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]), "Null album should always deny for mode: {$mode}");
		}
	}

	public function testCanBatchFaceOpsAdminAlwaysTrueWithAlbum(): void
	{
		$this->actingAs($this->admin);
		foreach (['public', 'private', 'privacy-preserving', 'restricted'] as $mode) {
			Configs::set('ai_vision_face_permission_mode', $mode);
			$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]), "Admin failed for mode: {$mode}");
		}
	}

	public function testCanBatchFaceOpsAiVisionDisabledDeniesNonAdmin(): void
	{
		Configs::set('ai_vision_enabled', '0');
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}
}
