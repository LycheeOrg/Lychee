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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RunFaceClusteringTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');

		config(['features.ai-vision-service.face-url' => 'http://fake-vision-service:8000']);
		config(['features.ai-vision-service.face-api-key' => 'test-api-key']);
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
		$response = $this->getJson('Maintenance::runFaceClustering');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::runFaceClustering');
		$this->assertForbidden($response);
	}

	public function testCheckReturnsOneWhenEnabled(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Maintenance::runFaceClustering');
		$this->assertOk($response);
		self::assertEquals(1, $response->json());
	}

	public function testCheckReturnsZeroWhenDisabled(): void
	{
		Configs::set('ai_vision_enabled', '0');

		$response = $this->actingAs($this->admin)->getJson('Maintenance::runFaceClustering');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::runFaceClustering');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::runFaceClustering');
		$this->assertForbidden($response);
	}

	public function testDoReturns503WhenServiceNotConfigured(): void
	{
		config(['features.ai-vision-service.face-url' => '']);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertStatus($response, 503);

		$json = $response->json();
		self::assertEquals('error', $json['status']);
	}

	public function testDoReturns202WhenClusteringAccepted(): void
	{
		Http::fake([
			'fake-vision-service:8000/cluster' => Http::response(
				['status' => 'accepted'],
				202
			),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertStatus($response, 202);

		$json = $response->json();
		self::assertEquals('dispatched', $json['status']);
		self::assertStringContainsString('accepted', $json['message']);
	}

	public function testDoReturns200OnSuccessfulClustering(): void
	{
		Http::fake([
			'fake-vision-service:8000/cluster' => Http::response(
				['status' => 'ok'],
				200
			),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals('dispatched', $json['status']);
	}

	public function testDoReturns503WhenServiceReturnsError(): void
	{
		Http::fake([
			'fake-vision-service:8000/cluster' => Http::response(null, 500),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertStatus($response, 503);

		$json = $response->json();
		self::assertEquals('error', $json['status']);
		self::assertStringContainsString('500', $json['message']);
	}

	public function testDoReturns503WhenConnectionFails(): void
	{
		Http::fake([
			'fake-vision-service:8000/cluster' => fn () => throw new \RuntimeException('Connection refused'),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertStatus($response, 503);

		$json = $response->json();
		self::assertEquals('error', $json['status']);
	}

	public function testDoSendsApiKeyHeader(): void
	{
		Http::fake([
			'fake-vision-service:8000/cluster' => Http::response(['status' => 'ok'], 200),
		]);

		$this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');

		Http::assertSent(fn ($request) => $request->hasHeader('X-API-Key', 'test-api-key') &&
			$request->url() === 'http://fake-vision-service:8000/cluster');
	}
}
