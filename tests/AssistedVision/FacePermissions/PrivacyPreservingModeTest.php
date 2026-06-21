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

use App\Contracts\Models\AbstractAlbum;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Policies\AiVisionPolicy;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * FacePermissionMode::PRIVACY_PRESERVING — photo/album owner + admin for most operations.
 *
 * Expected behaviour per operation:
 *   View People page   → admin only (global context returns false for non-admin)
 *   View face overlays → photo owner or album editor (canEdit)
 *   Create/edit Person → admin only (global context returns false for non-admin)
 *   Assign face        → photo: canEdit(photo); album: isAlbumOwner
 *   Trigger scan       → photo: canEdit(photo); album: isAlbumOwner
 *   Claim person       → logged users (mode-independent, config-gated)
 *   Merge persons      → admin only
 *   Dismiss face       → photo owner only (isOwner, all modes)
 *   Batch face ops     → album: isAlbumOwner; null album: admin only
 *   View album people  → album owner only (isAlbumOwner)
 */
class PrivacyPreservingModeTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Person $person2;
	private Face $face1;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');
		Configs::set('ai_vision_face_allow_user_claim', '1');

		$this->person1 = Person::factory()->with_name('Alice')->create();
		$this->person2 = Person::factory()->with_name('Bob')->create();
		$this->face1 = Face::factory()->for_photo($this->photo1)->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── View People page ─────────────────────────────────────────

	public function testViewPeopleGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleGateLoggedDenied(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleHttpLoggedForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('People');
		$this->assertForbidden($response);
	}

	public function testViewPeopleHttpAdminReturnsOk(): void
	{
		$response = $this->actingAs($this->admin)->getJson('People');
		$this->assertOk($response);
	}

	// ── View face overlays ───────────────────────────────────────

	public function testViewFaceOverlaysGateOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testViewFaceOverlaysGateAlbumEditorAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$fresh_photo = Photo::find($this->photo1->id);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $fresh_photo));
	}

	public function testViewFaceOverlaysGateNoAccessUserDenied(): void
	{
		$this->actingAs($this->userNoUpload);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testViewFaceOverlaysGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testViewFaceOverlaysGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	// ── Create/edit Person ───────────────────────────────────────

	public function testCreatePersonGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class));
	}

	public function testCreatePersonGateLoggedDenied(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class));
	}

	public function testCreatePersonGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class));
	}

	public function testCreatePersonHttpLoggedForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person', ['name' => 'Charlie']);
		$this->assertForbidden($response);
	}

	public function testCreatePersonHttpAdminCreated(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Person', ['name' => 'Charlie']);
		$this->assertCreated($response);
	}

	public function testEditPersonHttpLoggedForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Person/' . $this->person1->id, [
			'name' => 'Alice Updated',
		]);
		$this->assertForbidden($response);
	}

	public function testEditPersonHttpAdminAllowed(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Person/' . $this->person1->id, [
			'name' => 'Alice Updated',
		]);
		$this->assertOk($response);
	}

	public function testDeletePersonHttpLoggedForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeletePersonHttpAdminAllowed(): void
	{
		$response = $this->actingAs($this->admin)->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertNoContent($response);
	}

	// ── Assign face ──────────────────────────────────────────────

	public function testAssignFaceGatePhotoOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGatePhotoAlbumEditorAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$fresh_photo = Photo::find($this->photo1->id);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $fresh_photo));
	}

	public function testAssignFaceGatePhotoNoAccessDenied(): void
	{
		$this->actingAs($this->userNoUpload);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGatePhotoGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGateAlbumOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testAssignFaceGateAlbumNonOwnerDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testAssignFaceGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testAssignFaceHttpOwnerAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertStatus($response, [200, 201]);
	}

	public function testAssignFaceHttpNoAccessForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertForbidden($response);
	}

	// ── Trigger scan ─────────────────────────────────────────────

	public function testTriggerScanGatePhotoOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testTriggerScanGatePhotoAlbumEditorAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$fresh_photo = Photo::find($this->photo1->id);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $fresh_photo));
	}

	public function testTriggerScanGatePhotoNoAccessDenied(): void
	{
		$this->actingAs($this->userNoUpload);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testTriggerScanGatePhotoGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testTriggerScanGateAlbumOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testTriggerScanGateAlbumNonOwnerDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testTriggerScanGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	// ── Claim person ─────────────────────────────────────────────

	public function testClaimPersonGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_CLAIM_PERSON, Person::class));
	}

	public function testClaimPersonGateLoggedAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_CLAIM_PERSON, Person::class));
	}

	public function testClaimPersonGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_CLAIM_PERSON, Person::class));
	}

	public function testClaimPersonHttpGuestUnauthorized(): void
	{
		$response = $this->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertUnauthorized($response);
	}

	public function testClaimPersonHttpLoggedCreated(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertCreated($response);
	}

	// ── Merge persons ────────────────────────────────────────────

	public function testMergePersonsGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class));
	}

	public function testMergePersonsGateLoggedDenied(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class));
	}

	public function testMergePersonsGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class));
	}

	public function testMergePersonsHttpLoggedForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testMergePersonsHttpAdminAllowed(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertCreated($response);
	}

	// ── Dismiss face ─────────────────────────────────────────────

	public function testDismissFaceGatePhotoOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testDismissFaceGateAlbumEditorDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testDismissFaceGateNoAccessDenied(): void
	{
		$this->actingAs($this->userNoUpload);
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testDismissFaceGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testDismissFaceGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->photo1));
	}

	public function testDismissFaceHttpOwnerAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Face/' . $this->face1->id);
		$this->assertOk($response);
	}

	public function testDismissFaceHttpAlbumEditorForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Face/' . $this->face1->id);
		$this->assertForbidden($response);
	}

	public function testDismissFaceHttpGuestUnauthorized(): void
	{
		$response = $this->patchJson('Face/' . $this->face1->id);
		$this->assertUnauthorized($response);
	}

	// ── Batch face ops ───────────────────────────────────────────

	public function testBatchFaceOpsGateAlbumOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsGateAlbumNonOwnerDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsGateNullAlbumLoggedDenied(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]));
	}

	public function testBatchFaceOpsGateNullAlbumAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]));
	}

	public function testBatchFaceOpsGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsHttpOwnerAllowed(): void
	{
		$face2 = Face::factory()->for_photo($this->photo1)->without_crop()->create();
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $face2->id],
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertOk($response);
	}

	public function testBatchFaceOpsHttpNoAccessForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id],
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertForbidden($response);
	}

	// ── View album people ────────────────────────────────────────

	public function testViewAlbumPeopleGateOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleGateNonOwnerDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleHttpOwnerAllowed(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->without_crop()->create();
		$response = $this->actingAs($this->userMayUpload1)->getJson('Album/' . $this->album1->id . '/people');
		$this->assertOk($response);
	}

	public function testViewAlbumPeopleHttpNonOwnerForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->getJson('Album/' . $this->album1->id . '/people');
		$this->assertForbidden($response);
	}
}
