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
use App\Services\Image\FacialRecognitionService;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SyncFaceEmbeddingsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── CHECK (GET) ─────────────────────────────────────────────

	public function testCheckAsGuest(): void
	{
		$response = $this->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertForbidden($response);
	}

	public function testCheckReturnsZeroWhenAiVisionDisabled(): void
	{
		Configs::set('ai_vision_enabled', '0');

		$response = $this->actingAs($this->admin)->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsZeroWhenHealthReturnsNull(): void
	{
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')->willReturn(null);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsDifference(): void
	{
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')->willReturn([
			'status' => 'ok',
			'model_loaded' => true,
			'embedding_count' => 5,
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);

		$lychee_count = Face::count();
		self::assertEquals(abs($lychee_count - 5), $response->json());
	}

	public function testCheckReturnsZeroWhenInSync(): void
	{
		$face_count = Face::count();
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')->willReturn([
			'status' => 'ok',
			'model_loaded' => true,
			'embedding_count' => $face_count,
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::syncFaceEmbeddings');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::syncFaceEmbeddings');
		$this->assertForbidden($response);
	}

	public function testDoReturnsZeroWhenExportReturnsNull(): void
	{
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('syncFaceEmbeddings')->willReturn(null);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(0, $json['synced_count']);
		self::assertEquals(0, $json['missing_in_ai']);
	}

	public function testDoSyncsExistingFaces(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->create(['laplacian_variance' => 100.0]);

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('syncFaceEmbeddings')->willReturn([
			'count' => 1,
			'embeddings' => [
				[
					'lychee_face_id' => $face->id,
					'photo_id' => $this->photo1->id,
					'laplacian_variance' => 250.0,
					'crop_path' => 'faces/ab/cd/token.jpg',
				],
			],
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(1, $json['synced_count']);

		$face->refresh();
		self::assertEquals(250.0, $face->laplacian_variance);
	}

	public function testDoReportsMissingInAi(): void
	{
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('syncFaceEmbeddings')->willReturn([
			'count' => 0,
			'embeddings' => [],
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::syncFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(0, $json['synced_count']);
		self::assertGreaterThanOrEqual(3, $json['missing_in_ai']);
	}
}
