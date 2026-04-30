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
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->admin = User::factory()->may_administrate()->create();
		$this->nonAdmin = User::factory()->create();
		$this->photo = Photo::factory()->create(['owner_id' => $this->admin->id]);
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	// ── GET /Face/maintenance ─────────────────────────────────

	public function testListFacesSortedByConfidenceAscending(): void
	{
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.5, 'laplacian_variance' => 100.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.9, 'laplacian_variance' => 50.0]);
		Face::factory()->for_photo($this->photo)->create(['confidence' => 0.7, 'laplacian_variance' => 200.0]);

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?sort_by=confidence&sort_dir=asc');
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

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?sort_by=laplacian_variance&sort_dir=asc');
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
		// Default sort_by=confidence, sort_dir=asc: lowest confidence first
		self::assertLessThanOrEqual($data[1]['confidence'], $data[0]['confidence']);
	}

	public function testPaginationWorks(): void
	{
		Face::factory()->for_photo($this->photo)->count(5)->create();

		$response = $this->actingAs($this->admin)->getJson('Face/maintenance?per_page=2&page=1');
		$this->assertOk($response);

		$data = $response->json('data');
		$meta = $response->json('meta');
		self::assertCount(2, $data);
		self::assertEquals(5, $meta['total']);
		self::assertEquals(2, $meta['per_page']);
		self::assertEquals(3, $meta['last_page']);
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
}
