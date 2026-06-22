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

namespace Tests\AssistedVision\NsfwClassification;

use Illuminate\Support\Facades\Http;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class NsfwConfigControllerTest extends BaseApiWithDataTest
{
	public function testShowReturns503WhenNotConfigured(): void
	{
		config(['features.ai-vision-service.nsfw-url' => '']);

		$response = $this->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 503);
		self::assertArrayHasKey('error', $response->json());
	}

	public function testShowProxiesConfigFromService(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => Http::response([
				'presets' => ['default', 'strict'],
				'active_preset' => 'default',
			], 200),
		]);

		$response = $this->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertOk($response);
		self::assertEquals('default', $response->json('active_preset'));
	}

	public function testShowReturns502WhenServiceErrors(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => Http::response([], 500),
		]);

		$response = $this->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 502);
	}

	public function testShowReturns503WhenServiceUnreachable(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => Http::response(fn () => throw new \Exception('Connection refused')),
		]);

		$response = $this->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 503);
	}
}
