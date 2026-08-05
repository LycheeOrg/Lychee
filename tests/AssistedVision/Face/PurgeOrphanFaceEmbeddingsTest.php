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

class PurgeOrphanFaceEmbeddingsTest extends BaseApiWithDataTest
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
		$response = $this->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertForbidden($response);
	}

	public function testCheckReturnsZeroWhenAiVisionDisabled(): void
	{
		Configs::set('ai_vision_enabled', '0');

		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsZeroWhenHealthCheckFails(): void
	{
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')
			->willThrowException(new \App\Exceptions\ExternalComponentFailedException('Service unavailable'));
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsZeroWhenAiHasFewerOrEqualEmbeddings(): void
	{
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')->willReturn([
			'status' => 'ok',
			'model_loaded' => true,
			'embedding_count' => 3,
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsPositiveDifferenceWhenAiHasMoreEmbeddings(): void
	{
		$lychee_count = Face::count();

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('checkHealth')->willReturn([
			'status' => 'ok',
			'model_loaded' => true,
			'embedding_count' => $lychee_count + 5,
		]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);
		self::assertEquals(5, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertForbidden($response);
	}

	public function testDoReturnsZeroWhenPurgeReturnsNull(): void
	{
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->method('syncEmbeddingsBatch')->willReturn(['marked' => 0]);
		$mock->method('purgeAbsentEmbeddings')->willReturn(null);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(0, $json['purged_count']);
	}

	public function testDoSendsBatchesAndPurges(): void
	{
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->expects(self::atLeastOnce())
			->method('syncEmbeddingsBatch')
			->with(self::callback(fn ($face_ids) => is_array($face_ids)), self::callback(fn ($batch) => is_int($batch)))
			->willReturn(['marked' => 3]);
		$mock->method('purgeAbsentEmbeddings')->willReturn(['deleted' => 4]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(4, $json['purged_count']);
	}

	public function testDoSendsEmptyBatchWhenNoFacesExist(): void
	{
		$mock = $this->createMock(FacialRecognitionService::class);
		$mock->expects(self::once())
			->method('syncEmbeddingsBatch')
			->with([], 0)
			->willReturn(['marked' => 0]);
		$mock->method('purgeAbsentEmbeddings')->willReturn(['deleted' => 0]);
		$this->app->instance(FacialRecognitionService::class, $mock);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::purgeOrphanFaceEmbeddings');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(0, $json['purged_count']);
	}
}
