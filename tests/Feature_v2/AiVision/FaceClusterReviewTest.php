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

class FaceClusterReviewTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_person_is_searchable_default', '1');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── LIST CLUSTERS ───────────────────────────────────────────

	public function testListClusters(): void
	{
		// Create faces with cluster labels
		Face::factory()->for_photo($this->photo1)->with_cluster(1)->create();
		Face::factory()->for_photo($this->photo1)->with_cluster(1)->create();
		Face::factory()->for_photo($this->photo1)->with_cluster(2)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson('FaceDetection/clusters');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertGreaterThanOrEqual(2, count($data));
	}

	public function testListClustersExcludesAssigned(): void
	{
		$person = Person::factory()->create();

		// Assigned face should not appear
		Face::factory()->for_photo($this->photo1)->for_person($person)->with_cluster(1)->create();

		// Unassigned face should appear
		Face::factory()->for_photo($this->photo1)->with_cluster(2)->create();

		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters');
		$this->assertOk($response);

		$cluster_labels = collect($response->json('data'))->pluck('cluster_label')->all();
		self::assertNotContains(1, $cluster_labels);
		self::assertContains(2, $cluster_labels);
	}

	public function testListClustersExcludesDismissed(): void
	{
		Face::factory()->for_photo($this->photo1)->dismissed()->with_cluster(3)->create();

		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters');
		$this->assertOk($response);

		$cluster_labels = collect($response->json('data'))->pluck('cluster_label')->all();
		self::assertNotContains(3, $cluster_labels);
	}

	public function testListClustersAsGuestUnauthorized(): void
	{
		$response = $this->getJson('FaceDetection/clusters');
		$this->assertUnauthorized($response);
	}

	// ── ASSIGN CLUSTER ──────────────────────────────────────────

	public function testAssignClusterToNewPerson(): void
	{
		Face::factory()->for_photo($this->photo1)->with_cluster(5)->count(3)->create();

		$response = $this->actingAs($this->admin)->postJson('FaceDetection/clusters/5/assign', [
			'new_person_name' => 'Cluster Person',
		]);
		$this->assertOk($response);
		self::assertEquals(3, $response->json('assigned_count'));

		// Verify person was created
		$person = Person::where('name', 'Cluster Person')->first();
		self::assertNotNull($person);

		// All faces should have person_id set
		$faces = Face::where('cluster_label', 5)->get();
		foreach ($faces as $face) {
			self::assertEquals($person->id, $face->person_id);
		}
	}

	public function testAssignClusterToExistingPerson(): void
	{
		$person = Person::factory()->with_name('Existing Person')->create();
		Face::factory()->for_photo($this->photo1)->with_cluster(6)->count(2)->create();

		$response = $this->actingAs($this->admin)->postJson('FaceDetection/clusters/6/assign', [
			'person_id' => $person->id,
		]);
		$this->assertOk($response);
		self::assertEquals(2, $response->json('assigned_count'));
	}

	public function testAssignClusterNotFound(): void
	{
		$response = $this->actingAs($this->admin)->postJson('FaceDetection/clusters/999/assign', [
			'new_person_name' => 'Test',
		]);
		$this->assertOk($response);
		self::assertEquals(0, $response->json('assigned_count'));
	}

	// ── DISMISS CLUSTER ─────────────────────────────────────────

	public function testDismissCluster(): void
	{
		Face::factory()->for_photo($this->photo1)->with_cluster(7)->count(3)->create();

		$response = $this->actingAs($this->admin)->postJson('FaceDetection/clusters/7/dismiss');
		$this->assertOk($response);
		self::assertEquals(3, $response->json('dismissed_count'));

		// All faces should be dismissed
		$faces = Face::where('cluster_label', 7)->get();
		foreach ($faces as $face) {
			self::assertTrue($face->is_dismissed);
		}
	}
}
