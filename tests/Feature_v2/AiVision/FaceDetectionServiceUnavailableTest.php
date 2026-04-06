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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FaceDetectionServiceUnavailableTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		config(['features.ai-vision.face-url' => 'http://fake-vision-service:8000']);
		config(['features.ai-vision.face-api-key' => 'test-api-key']);
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	public function testClusteringWhenServiceUnavailable(): void
	{
		Http::fake([
			'fake-vision-service:8000/*' => Http::response(null, 503),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::runFaceClustering');
		$this->assertStatus($response, [500, 503]);
	}

	public function testOtherEndpointsContinueWorking(): void
	{
		Http::fake([
			'fake-vision-service:8000/*' => Http::response(null, 503),
		]);

		// Other Lychee endpoints should still work fine
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);
	}
}
