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
 * FacePermissionMode::PUBLIC — the most permissive mode.
 *
 * Expected behaviour per operation:
 *   View People page   → guest
 *   View face overlays → album access (canSee)
 *   Create/edit Person → logged users
 *   Assign face        → logged users
 *   Trigger scan       → logged users
 *   Claim person       → logged users (+ config)
 *   Merge persons      → logged users
 *   Dismiss face       → photo owner + admin
 *   Batch face ops     → logged users
 *   View album people  → album access (canAccess)
 */
class PublicModeTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Person $person2;
	private Face $face1;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
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

	public function testViewPeopleGateGuestAllowed(): void
	{
		Auth::logout();
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleGateLoggedAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_VIEW_PEOPLE, Person::class));
	}

	public function testViewPeopleHttpGuestReturnsOk(): void
	{
		$response = $this->getJson('People');
		$this->assertOk($response);
	}

	public function testViewPeopleHttpLoggedReturnsOk(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('People');
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

	public function testViewFaceOverlaysGateGuestOnPrivateAlbumDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo1));
	}

	public function testViewFaceOverlaysGateNoAccessUserDenied(): void
	{
		$this->actingAs($this->userNoUpload);
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

	public function testCreatePersonGateLoggedAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class));
	}

	public function testCreatePersonGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_EDIT_PERSON, Person::class));
	}

	public function testCreatePersonHttpGuestUnauthorized(): void
	{
		$response = $this->postJson('Person', ['name' => 'Charlie']);
		$this->assertUnauthorized($response);
	}

	public function testCreatePersonHttpLoggedCreated(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person', ['name' => 'Charlie']);
		$this->assertCreated($response);
	}

	public function testEditPersonHttpAdminAllowed(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Person/' . $this->person1->id, [
			'name' => 'Alice Updated',
		]);
		$this->assertOk($response);
	}

	public function testEditPersonHttpGuestUnauthorized(): void
	{
		$response = $this->patchJson('Person/' . $this->person1->id, ['name' => 'Hacker']);
		$this->assertUnauthorized($response);
	}

	public function testDeletePersonHttpLoggedAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertNoContent($response);
	}

	public function testDeletePersonHttpGuestUnauthorized(): void
	{
		$response = $this->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertUnauthorized($response);
	}

	// ── Assign face ──────────────────────────────────────────────

	public function testAssignFaceGatePhotoOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGateLoggedNonOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceGateAlbumOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testAssignFaceGateAlbumLoggedNonOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_ASSIGN_FACE_IN_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testAssignFaceGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $this->photo1));
	}

	public function testAssignFaceHttpGuestUnauthorized(): void
	{
		$response = $this->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testAssignFaceHttpLoggedAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertStatus($response, [200, 201]);
	}

	// ── Trigger scan ─────────────────────────────────────────────

	public function testTriggerScanGatePhotoOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
	}

	public function testTriggerScanGatePhotoLoggedNonOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $this->photo1));
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

	public function testTriggerScanGateAlbumLoggedNonOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_TRIGGER_SCAN_ON_ALBUM, [AbstractAlbum::class, $this->album1]));
	}

	public function testTriggerScanGateAlbumGuestDenied(): void
	{
		Auth::logout();
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

	public function testMergePersonsGateLoggedAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class));
	}

	public function testMergePersonsGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AiVisionPolicy::CAN_MERGE_PERSONS, Person::class));
	}

	public function testMergePersonsHttpGuestUnauthorized(): void
	{
		$response = $this->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testMergePersonsHttpLoggedAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/merge', [
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

	public function testDismissFaceGateNonOwnerDenied(): void
	{
		$this->actingAs($this->userMayUpload2);
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

	public function testDismissFaceHttpNonOwnerForbidden(): void
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

	public function testBatchFaceOpsGateLoggedNonOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsGateGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
	}

	public function testBatchFaceOpsGateNullAlbumLoggedAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]));
	}

	public function testBatchFaceOpsGateNullAlbumGuestDenied(): void
	{
		Auth::logout();
		$this->assertFalse(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]));
	}

	public function testBatchFaceOpsGateAdminAllowed(): void
	{
		$this->actingAs($this->admin);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, $this->album1]));
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]));
	}

	public function testBatchFaceOpsHttpLoggedAllowed(): void
	{
		$face2 = Face::factory()->for_photo($this->photo1)->without_crop()->create();
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $face2->id],
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertOk($response);
	}

	public function testBatchFaceOpsHttpGuestUnauthorized(): void
	{
		$response = $this->postJson('Face/batch', [
			'face_ids' => [$this->face1->id],
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertUnauthorized($response);
	}

	// ── View album people ────────────────────────────────────────

	public function testViewAlbumPeopleGateOwnerAllowed(): void
	{
		$this->actingAs($this->userMayUpload1);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleGateAlbumEditorAllowed(): void
	{
		$this->actingAs($this->userMayUpload2);
		$this->assertTrue(Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album1]));
	}

	public function testViewAlbumPeopleGateGuestOnPrivateAlbumDenied(): void
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
}
