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
use App\Models\Face;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FaceDismissTest extends BaseApiWithDataTest
{
	private Face $face1;
	private Face $face2;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->face1 = Face::factory()->for_photo($this->photo1)->create();
		$this->face2 = Face::factory()->for_photo($this->photo1)->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── TOGGLE DISMISSED ────────────────────────────────────────

	public function testToggleDismissedByPhotoOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Face/' . $this->face1->id);
		$this->assertOk($response);
		self::assertTrue($response->json('is_dismissed'));

		// Toggle back
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Face/' . $this->face1->id);
		$this->assertOk($response);
		self::assertFalse($response->json('is_dismissed'));
	}

	public function testToggleDismissedByNonOwnerForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Face/' . $this->face1->id);
		$this->assertForbidden($response);
	}

	public function testToggleDismissedByAdmin(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Face/' . $this->face1->id);
		$this->assertOk($response);
		self::assertTrue($response->json('is_dismissed'));
	}

	public function testToggleDismissedAsGuestUnauthorized(): void
	{
		$response = $this->patchJson('Face/' . $this->face1->id);
		$this->assertUnauthorized($response);
	}

	// ── DESTROY DISMISSED ───────────────────────────────────────

	public function testDestroyDismissedAsAdmin(): void
	{
		// Dismiss some faces first
		$this->face1->is_dismissed = true;
		$this->face1->save();

		$response = $this->actingAs($this->admin)->deleteJson('Face/dismissed');
		$this->assertOk($response);
		self::assertEquals(1, $response->json('deleted_count'));

		// face1 should be deleted, face2 should still exist
		self::assertNull(Face::find($this->face1->id));
		self::assertNotNull(Face::find($this->face2->id));
	}

	public function testDestroyDismissedAsUserForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Face/dismissed');
		$this->assertForbidden($response);
	}

	public function testDestroyDismissedAsGuestUnauthorized(): void
	{
		$response = $this->deleteJson('Face/dismissed');
		$this->assertUnauthorized($response);
	}

	public function testDestroyDismissedNoneToDelete(): void
	{
		$response = $this->actingAs($this->admin)->deleteJson('Face/dismissed');
		$this->assertOk($response);
		self::assertEquals(0, $response->json('deleted_count'));
	}
}
