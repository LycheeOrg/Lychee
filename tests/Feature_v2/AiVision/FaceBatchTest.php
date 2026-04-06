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

class FaceBatchTest extends BaseApiWithDataTest
{
	private Person $person1;
	private Person $person2;
	private Face $face1;
	private Face $face2;
	private Face $face3;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->person1 = Person::factory()->with_name('Alice')->create();
		$this->person2 = Person::factory()->with_name('Bob')->create();
		$this->face1 = Face::factory()->for_photo($this->photo1)->without_crop()->create();
		$this->face2 = Face::factory()->for_photo($this->photo1)->without_crop()->create();
		$this->face3 = Face::factory()->for_photo($this->photo2)->without_crop()->create();
	}

	public function tearDown(): void
	{
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── BATCH ASSIGN TO EXISTING PERSON ──────────────────────────

	public function testBatchAssignToExistingPerson(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $this->face2->id],
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(2, $response->json('affected_count'));
		self::assertEquals($this->person1->id, $response->json('person_id'));

		// Verify faces were assigned
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertEquals($this->person1->id, $this->face1->person_id);
		self::assertEquals($this->person1->id, $this->face2->person_id);
	}

	public function testBatchAssignCreatesNewPerson(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $this->face2->id],
			'action' => 'assign',
			'new_person_name' => 'Charlie',
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(2, $response->json('affected_count'));
		self::assertNotNull($response->json('person_id'));

		// Verify new person was created
		$new_person = Person::find($response->json('person_id'));
		self::assertNotNull($new_person);
		self::assertEquals('Charlie', $new_person->name);

		// Verify faces were assigned
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertEquals($new_person->id, $this->face1->person_id);
		self::assertEquals($new_person->id, $this->face2->person_id);
	}

	public function testBatchAssignRequiresPersonIdOrName(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id],
			'action' => 'assign',
		]);
		$this->assertStatus($response, 422);
	}

	// ── BATCH UNASSIGN ───────────────────────────────────────────

	public function testBatchUnassign(): void
	{
		// Assign faces first
		$this->face1->person_id = $this->person1->id;
		$this->face1->save();
		$this->face2->person_id = $this->person2->id;
		$this->face2->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $this->face2->id],
			'action' => 'unassign',
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(2, $response->json('affected_count'));
		self::assertNull($response->json('person_id'));

		// Verify faces were unassigned
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertNull($this->face1->person_id);
		self::assertNull($this->face2->person_id);
	}

	public function testBatchUnassignPartial(): void
	{
		// Only face1 is assigned
		$this->face1->person_id = $this->person1->id;
		$this->face1->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id, $this->face2->id],
			'action' => 'unassign',
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(2, $response->json('affected_count'));

		// Verify both faces are now unassigned
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertNull($this->face1->person_id);
		self::assertNull($this->face2->person_id);
	}

	// ── BATCH VALIDATION ──────────────────────────────────────────

	public function testBatchRequiresFaceIds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'action' => 'assign',
			'person_id' => $this->person1->id,
		]);
		$this->assertStatus($response, 422);
	}

	public function testBatchRequiresAction(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id],
		]);
		$this->assertStatus($response, 422);
	}

	public function testBatchRejectsInvalidAction(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Face/batch', [
			'face_ids' => [$this->face1->id],
			'action' => 'delete',
		]);
		$this->assertStatus($response, 422);
	}

	// ── UNCLUSTER FACES ───────────────────────────────────────────

	public function testUnclusterFacesFromCluster(): void
	{
		// Set up a cluster
		$this->face1->cluster_label = 42;
		$this->face1->save();
		$this->face2->cluster_label = 42;
		$this->face2->save();
		$this->face3->cluster_label = 42;
		$this->face3->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('clusters/42/uncluster', [
			'face_ids' => [$this->face1->id, $this->face2->id],
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(2, $response->json('unclustered_count'));

		// Verify faces were unclustered
		$this->face1->refresh();
		$this->face2->refresh();
		$this->face3->refresh();
		self::assertNull($this->face1->cluster_label);
		self::assertNull($this->face2->cluster_label);
		self::assertEquals(42, $this->face3->cluster_label); // unchanged
	}

	public function testUnclusterIgnoresAlreadyAssignedFaces(): void
	{
		// Set up a cluster with one face assigned to a person
		$this->face1->cluster_label = 42;
		$this->face1->person_id = $this->person1->id;
		$this->face1->save();
		$this->face2->cluster_label = 42;
		$this->face2->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('clusters/42/uncluster', [
			'face_ids' => [$this->face1->id, $this->face2->id],
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(1, $response->json('unclustered_count')); // only face2

		// Verify only unassigned face was unclustered
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertEquals(42, $this->face1->cluster_label); // unchanged (assigned)
		self::assertNull($this->face2->cluster_label); // unclustered
	}

	public function testUnclusterIgnoresDismissedFaces(): void
	{
		// Set up a cluster with one face dismissed
		$this->face1->cluster_label = 42;
		$this->face1->is_dismissed = true;
		$this->face1->save();
		$this->face2->cluster_label = 42;
		$this->face2->save();

		$response = $this->actingAs($this->userMayUpload1)->postJson('clusters/42/uncluster', [
			'face_ids' => [$this->face1->id, $this->face2->id],
		]);
		$this->assertStatus($response, 200);
		self::assertEquals(1, $response->json('unclustered_count')); // only face2

		// Verify only non-dismissed face was unclustered
		$this->face1->refresh();
		$this->face2->refresh();
		self::assertEquals(42, $this->face1->cluster_label); // unchanged (dismissed)
		self::assertNull($this->face2->cluster_label); // unclustered
	}

	public function testUnclusterRequiresFaceIds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('clusters/42/uncluster', []);
		$this->assertStatus($response, 422);
	}
}
