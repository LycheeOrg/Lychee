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

class FaceClusterFacesTest extends BaseApiWithDataTest
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

	// ── GET /FaceDetection/clusters/{label}/faces ────────────────

	public function testGetFacesForCluster(): void
	{
		Face::factory()->for_photo($this->photo1)->with_cluster(10)->count(3)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson('FaceDetection/clusters/10/faces');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(3, $data);
	}

	public function testGetFacesForClusterReturns404ForUnknownCluster(): void
	{
		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters/9999/faces');
		$this->assertNotFound($response);
	}

	public function testGetFacesExcludesAssignedFaces(): void
	{
		$person = Person::factory()->create();
		Face::factory()->for_photo($this->photo1)->for_person($person)->with_cluster(20)->create();
		// Only an unassigned face in cluster 20 should be returned
		Face::factory()->for_photo($this->photo1)->with_cluster(20)->create();

		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters/20/faces');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(1, $data);
		self::assertNull($data[0]['person_id']);
	}

	public function testGetFacesExcludesDismissedFaces(): void
	{
		Face::factory()->for_photo($this->photo1)->dismissed()->with_cluster(30)->create();
		// The dismissed face above should cause 404 since no qualifying faces remain
		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters/30/faces');
		$this->assertNotFound($response);
	}

	public function testGetFacesAsGuestUnauthorized(): void
	{
		$response = $this->getJson('FaceDetection/clusters/10/faces');
		$this->assertUnauthorized($response);
	}

	public function testGetFacesPaginated(): void
	{
		Face::factory()->for_photo($this->photo1)->with_cluster(40)->count(5)->create();

		$response = $this->actingAs($this->admin)->getJson('FaceDetection/clusters/40/faces?page=1');
		$this->assertOk($response);

		$data = $response->json('data');
		self::assertCount(5, $data);

		$firstFace = $data[0];
		self::assertArrayHasKey('id', $firstFace);
		self::assertArrayHasKey('photo_id', $firstFace);
		self::assertArrayHasKey('confidence', $firstFace);
		self::assertArrayHasKey('is_dismissed', $firstFace);
		self::assertFalse($firstFace['is_dismissed']);
	}
}
