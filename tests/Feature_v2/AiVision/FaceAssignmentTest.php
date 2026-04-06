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
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FaceAssignmentTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Face $face1;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_person_is_searchable_default', '1');

		$this->person1 = Person::factory()->with_name('Alice')->create();
		$this->face1 = Face::factory()->for_photo($this->photo1)->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── ASSIGN TO EXISTING PERSON ───────────────────────────────

	public function testAssignToExistingPerson(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertStatus($response, [200, 201]);
		self::assertEquals($this->person1->id, $response->json('person_id'));
	}

	public function testAssignCreatingNewPerson(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'new_person_name' => 'Charlie',
		]);
		$this->assertStatus($response, [200, 201]);
		self::assertNotNull($response->json('person_id'));

		// Verify new person was created
		$new_person = Person::find($response->json('person_id'));
		self::assertNotNull($new_person);
		self::assertEquals('Charlie', $new_person->name);
		self::assertTrue($new_person->is_searchable);
	}

	public function testReassignFaceToDifferentPerson(): void
	{
		$this->face1->person_id = $this->person1->id;
		$this->face1->save();

		$person2 = Person::factory()->with_name('Bob')->create();

		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $person2->id,
		]);
		$this->assertStatus($response, [200, 201]);
		self::assertEquals($person2->id, $response->json('person_id'));
	}

	public function testAssignValidationRequiresPersonOrName(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Face/' . $this->face1->id . '/assign', []);
		$this->assertUnprocessable($response);
	}

	public function testAssignAsGuestUnauthorized(): void
	{
		$response = $this->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testAssignRestrictedModeAsUserForbidden(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => $this->person1->id,
		]);
		$this->assertForbidden($response);
	}

	public function testAssignInvalidPersonId(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Face/' . $this->face1->id . '/assign', [
			'person_id' => 'nonexistent_person_id',
		]);
		$this->assertUnprocessable($response);
	}
}
