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

namespace Tests\AssistedVision\Face;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use Tests\Feature_v2\Base\BaseApiTest;

class FaceMaintenanceTest extends BaseApiTest
{
	protected User $admin;
	protected User $nonAdmin;
	protected Photo $photo;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->admin = User::factory()->may_administrate()->create();
		$this->nonAdmin = User::factory()->create();
		$this->photo = Photo::factory()->create(['owner_id' => $this->admin->id]);
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	// ── GET /Face/maintenance ─────────────────────────────────

	public function testListFacesSortedByConfidenceAscending(): void
	{
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.5, 'laplacian_variance' => 100.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.9, 'laplacian_variance' => 50.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.7, 'laplacian_variance' => 200.0]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?sort_by=confidence&sort_dir=ASC');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(3, $data);
		self::assertLessThanOrEqual($data[1]['confidence'], $data[0]['confidence']);
		self::assertLessThanOrEqual($data[2]['confidence'], $data[1]['confidence']);
	}

	public function testListFacesSortedByLaplacianVarianceAscending(): void
	{
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.8, 'laplacian_variance' => 300.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.6, 'laplacian_variance' => 10.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.7, 'laplacian_variance' => 150.0]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?sort_by=laplacian_variance&sort_dir=ASC');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(3, $data);
		self::assertLessThanOrEqual($data[1]['laplacian_variance'], $data[0]['laplacian_variance']);
		self::assertLessThanOrEqual($data[2]['laplacian_variance'], $data[1]['laplacian_variance']);
	}

	public function testDefaultSortIsConfidenceAsc(): void
	{
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.2]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.9]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(2, $data);
		// Default sort_by=confidence, sort_dir=ASC: lowest confidence first
		self::assertLessThanOrEqual($data[1]['confidence'], $data[0]['confidence']);
	}

	public function testDefaultListExcludesDismissedFaces(): void
	{
		Face::factory()->for_photo($this->photo)->create(['is_dismissed' => false]);
		Face::factory()->for_photo($this->photo)->create(['is_dismissed' => true]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertGreaterThan(0, count($data));
		foreach ($data as $face) {
			self::assertFalse($face['is_dismissed']);
		}
	}

	public function testDismissedOnlyFilterReturnsOnlyDismissedFaces(): void
	{
		Face::factory()->for_photo($this->photo)->create(['is_dismissed' => false]);
		Face::factory()->for_photo($this->photo)->create(['is_dismissed' => true]);
		Face::factory()->for_photo($this->photo)->create(['is_dismissed' => true]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?dismissed_only=1');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertGreaterThan(0, count($data));
		foreach ($data as $face) {
			self::assertTrue($face['is_dismissed']);
		}
	}

	public function testPaginationWorks(): void
	{
		Face::factory()->for_photo($this->photo)->count(5)->create();

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?per_page=2&page=1');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(2, $data);
		self::assertEquals(5, $response->json('total'));
		self::assertEquals(2, $response->json('per_page'));
		self::assertEquals(3, $response->json('last_page'));
	}

	public function testAdminOnlyNonAdminGetsForbidden(): void
	{
		$response = $this->actingAs($this->nonAdmin)->getJson('Face/maintenance');
		$this->assertForbidden($response);
	}

	public function testResponseIncludesPersonNameAndClusterLabel(): void
	{
		Face::factory()->for_photo($this->photo)->with_cluster(7)->create(['confidence' => 0.8]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(1, $data);
		self::assertEquals(0.8, $data[0]['confidence']);
		self::assertEquals(7, $data[0]['cluster_label']);
		self::assertArrayHasKey('laplacian_variance', $data[0]);
	}

	// ── POST /Face/maintenance/batch-assign ───────────────────

	public function testBatchAssignToExistingPerson(): void
	{
		$person = Person::factory()->with_name('Existing Person')->create();
		$face1 = Face::factory()->for_photo($this->photo)->create();
		$face2 = Face::factory()->for_photo($this->photo)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face1->id, $face2->id],
			'person_id' => $person->id,
		]);
		$this->assertOk($response);
		self::assertEquals(2, $response->json('assigned_count'));
		self::assertEquals($person->id, $response->json('person_id'));

		$face1->refresh();
		$face2->refresh();
		self::assertEquals($person->id, $face1->person_id);
		self::assertEquals($person->id, $face2->person_id);

		$person->refresh();
		self::assertEquals(2, $person->face_count);
		self::assertEquals(1, $person->photo_count);
	}

	public function testBatchAssignCreatesNewPerson(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
			'new_person_name' => 'Brand New Person',
		]);
		$this->assertOk($response);
		self::assertEquals(1, $response->json('assigned_count'));

		$person = Person::find($response->json('person_id'));
		self::assertNotNull($person);
		self::assertEquals('Brand New Person', $person->name);

		self::assertEquals($person->id, $face->fresh()->person_id);
	}

	public function testBatchAssignReassignsAndUpdatesOldPersonCounters(): void
	{
		$oldPerson = Person::factory()->create();
		$newPerson = Person::factory()->create();
		$faceToMove = Face::factory()->for_photo($this->photo)->for_person($oldPerson)->create();
		Face::factory()->for_photo($this->photo)->for_person($oldPerson)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$faceToMove->id],
			'person_id' => $newPerson->id,
		]);
		$this->assertOk($response);

		// Old person keeps its remaining face, counters decremented.
		$oldPerson->refresh();
		self::assertEquals(1, $oldPerson->face_count);
		self::assertEquals(1, $oldPerson->photo_count);

		// New person gains the moved face.
		$newPerson->refresh();
		self::assertEquals(1, $newPerson->face_count);
		self::assertEquals(1, $newPerson->photo_count);
	}

	public function testBatchAssignDeletesOldPersonWhenLastFaceMoved(): void
	{
		$oldPerson = Person::factory()->create();
		$newPerson = Person::factory()->create();
		$face = Face::factory()->for_photo($this->photo)->for_person($oldPerson)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
			'person_id' => $newPerson->id,
		]);
		$this->assertOk($response);

		self::assertNull(Person::find($oldPerson->id));

		$newPerson->refresh();
		self::assertEquals(1, $newPerson->face_count);
		self::assertEquals(1, $newPerson->photo_count);
	}

	public function testBatchAssignWithNonexistentFaceIdsAssignsZero(): void
	{
		$person = Person::factory()->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => ['does-not-exist'],
			'person_id' => $person->id,
		]);
		$this->assertOk($response);
		self::assertEquals(0, $response->json('assigned_count'));
	}

	public function testBatchAssignWithInvalidPersonIdReturnsNotFound(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
			'person_id' => 'does-not-exist',
		]);
		$this->assertNotFound($response);
	}

	public function testBatchAssignRequiresFaceIds(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'new_person_name' => 'No Faces',
		]);
		$this->assertUnprocessable($response);
	}

	public function testBatchAssignRequiresPersonIdOrNewPersonName(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$response = $this->actingAs($this->admin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
		]);
		$this->assertUnprocessable($response);
	}

	public function testBatchAssignAdminOnlyNonAdminGetsForbidden(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$response = $this->actingAs($this->nonAdmin)->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
			'new_person_name' => 'Nope',
		]);
		$this->assertForbidden($response);
	}

	public function testBatchAssignGuestUnauthorized(): void
	{
		$face = Face::factory()->for_photo($this->photo)->create();

		$response = $this->postJson('Face/maintenance/batch-assign', [
			'face_ids' => [$face->id],
			'new_person_name' => 'Nope',
		]);
		$this->assertUnauthorized($response);
	}
}
