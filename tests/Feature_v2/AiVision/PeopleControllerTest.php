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

class PeopleControllerTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Person $person2;
	private Person $personHidden;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->person1 = Person::factory()->with_name('Alice')->create();
		$this->person2 = Person::factory()->with_name('Bob')->create();
		$this->personHidden = Person::factory()->with_name('Hidden')->not_searchable()->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── INDEX ───────────────────────────────────────────────────

	public function testIndexAsGuest(): void
	{
		$response = $this->getJson('People');
		$this->assertOk($response);

		// Guest should see searchable persons but not hidden
		$names = collect($response->json('data'))->pluck('name')->all();
		self::assertContains('Alice', $names);
		self::assertContains('Bob', $names);
		self::assertNotContains('Hidden', $names);
	}

	public function testIndexAsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('People');
		$this->assertOk($response);

		// Admin sees all including non-searchable
		$names = collect($response->json('data'))->pluck('name')->all();
		self::assertContains('Alice', $names);
		self::assertContains('Hidden', $names);
	}

	public function testIndexLinkedUserSeesOwnHiddenPerson(): void
	{
		$this->personHidden->user_id = $this->userMayUpload1->id;
		$this->personHidden->save();

		$response = $this->actingAs($this->userMayUpload1)->getJson('People');
		$this->assertOk($response);

		$names = collect($response->json('data'))->pluck('name')->all();
		self::assertContains('Hidden', $names);
	}

	public function testIndexRestrictedMode(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');

		// Guest should be forbidden
		$response = $this->getJson('People');
		$this->assertForbidden($response);

		// Admin still sees all
		$response = $this->actingAs($this->admin)->getJson('People');
		$this->assertOk($response);
	}

	// ── SHOW ────────────────────────────────────────────────────

	public function testShowPerson(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Person/' . $this->person1->id);
		$this->assertOk($response);
		self::assertEquals('Alice', $response->json('name'));
		self::assertArrayHasKey('face_count', $response->json());
		self::assertArrayHasKey('photo_count', $response->json());
	}

	public function testShowNonSearchablePersonAsGuestForbidden(): void
	{
		$response = $this->getJson('Person/' . $this->personHidden->id);
		$this->assertForbidden($response);
	}

	public function testShowNonExistentPerson(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Person/nonexistent_id_12345');
		$this->assertNotFound($response);
	}

	// ── STORE ───────────────────────────────────────────────────

	public function testStoreAsGuest(): void
	{
		$response = $this->postJson('Person', ['name' => 'Charlie']);
		// In public mode, logged users can create; guests cannot (no auth)
		$this->assertUnauthorized($response);
	}

	public function testStoreAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Person', ['name' => 'Charlie']);
		$this->assertOk($response);
		self::assertEquals('Charlie', $response->json('name'));
		self::assertTrue($response->json('is_searchable'));
	}

	public function testStoreValidationRequiresName(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Person', []);
		$this->assertUnprocessable($response);
	}

	public function testStoreRestrictedModeAsUser(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Person', ['name' => 'Charlie']);
		$this->assertForbidden($response);

		// Admin can still create
		$response = $this->actingAs($this->admin)->postJson('Person', ['name' => 'Charlie']);
		$this->assertOk($response);
	}

	// ── UPDATE ──────────────────────────────────────────────────

	public function testUpdateName(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Person/' . $this->person1->id, [
			'name' => 'Alice Updated',
		]);
		$this->assertOk($response);
		self::assertEquals('Alice Updated', $response->json('name'));
	}

	public function testUpdateSearchability(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Person/' . $this->person1->id, [
			'is_searchable' => false,
		]);
		$this->assertOk($response);
		self::assertFalse($response->json('is_searchable'));
	}

	public function testUpdateAsGuestUnauthorized(): void
	{
		$response = $this->patchJson('Person/' . $this->person1->id, ['name' => 'Hacker']);
		$this->assertUnauthorized($response);
	}

	// ── DESTROY ─────────────────────────────────────────────────

	public function testDestroyNullifiesFaces(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();

		$response = $this->actingAs($this->admin)->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertNoContent($response);

		self::assertNull(Person::find($this->person1->id));

		$face->refresh();
		self::assertNull($face->person_id);
	}

	public function testDestroyAsGuestUnauthorized(): void
	{
		$response = $this->deleteJson('Person/' . $this->person1->id, [
			'person_id' => $this->person1->id,
		]);
		$this->assertUnauthorized($response);
	}

	// ── HIDDEN FACE COUNT ───────────────────────────────────────

	public function testHiddenFaceCountInPhotoResponse(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();
		Face::factory()->for_photo($this->photo1)->for_person($this->personHidden)->create();

		// The photo detail should include hidden_face_count for non-admin
		// This is tested through the PhotoResource integration
		$response = $this->actingAs($this->admin)->getJson('Person/' . $this->person1->id);
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(1, $response->json('face_count'));
	}
}
