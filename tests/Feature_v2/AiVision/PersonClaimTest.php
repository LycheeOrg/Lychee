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
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PersonClaimTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Person $person2;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_allow_user_claim', '1');

		$this->person1 = Person::factory()->with_name('Alice')->create();
		$this->person2 = Person::factory()->with_name('Bob')->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── CLAIM ───────────────────────────────────────────────────

	public function testClaimSuccess(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertOk($response);
		self::assertEquals($this->userMayUpload1->id, $response->json('user_id'));
	}

	public function testClaimAlreadyClaimedByAnotherUserConflict(): void
	{
		$this->person1->user_id = $this->userMayUpload2->id;
		$this->person1->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertConflict($response);
	}

	public function testClaimAlreadyHaveDifferentPersonConflict(): void
	{
		// User already has person1 claimed
		$this->person1->user_id = $this->userMayUpload1->id;
		$this->person1->save();

		// Trying to claim person2 should conflict
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person2->id . '/claim');
		$this->assertConflict($response);
	}

	public function testAdminForceClaim(): void
	{
		// Person1 is already claimed by userMayUpload2
		$this->person1->user_id = $this->userMayUpload2->id;
		$this->person1->save();

		// Admin can override existing claim
		$response = $this->actingAs($this->admin)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertOk($response);
		self::assertEquals($this->admin->id, $response->json('user_id'));
	}

	public function testClaimAsGuestUnauthorized(): void
	{
		$response = $this->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertUnauthorized($response);
	}

	public function testClaimDisabledForNonAdmin(): void
	{
		Configs::set('ai_vision_face_allow_user_claim', '0');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertForbidden($response);

		// Admin still can claim
		$response = $this->actingAs($this->admin)->postJson('Person/' . $this->person1->id . '/claim');
		$this->assertOk($response);
	}

	// ── UNCLAIM ─────────────────────────────────────────────────

	public function testUnclaim(): void
	{
		$this->person1->user_id = $this->userMayUpload1->id;
		$this->person1->save();

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Person/' . $this->person1->id . '/claim');
		$this->assertNoContent($response);

		$this->person1->refresh();
		self::assertNull($this->person1->user_id);
	}

	public function testUnclaimOtherUserForbidden(): void
	{
		$this->person1->user_id = $this->userMayUpload1->id;
		$this->person1->save();

		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Person/' . $this->person1->id . '/claim');
		$this->assertForbidden($response);
	}

	public function testAdminCanUnclaim(): void
	{
		$this->person1->user_id = $this->userMayUpload1->id;
		$this->person1->save();

		$response = $this->actingAs($this->admin)->deleteJson('Person/' . $this->person1->id . '/claim');
		$this->assertNoContent($response);

		$this->person1->refresh();
		self::assertNull($this->person1->user_id);
	}

	// ── MERGE ───────────────────────────────────────────────────

	public function testMerge(): void
	{
		$face = \App\Models\Face::factory()->for_photo($this->photo1)->for_person($this->person2)->create();

		$response = $this->actingAs($this->admin)->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertOk($response);

		// Source person deleted
		self::assertNull(Person::find($this->person2->id));

		// Face reassigned to target
		$face->refresh();
		self::assertEquals($this->person1->id, $face->person_id);
	}

	public function testMergeAsGuestUnauthorized(): void
	{
		$response = $this->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testMergeRestrictedAsUserForbidden(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => $this->person2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testMergeInvalidSourcePerson(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Person/' . $this->person1->id . '/merge', [
			'source_person_id' => 'nonexistent_12345678',
		]);
		$this->assertUnprocessable($response);
	}
}
