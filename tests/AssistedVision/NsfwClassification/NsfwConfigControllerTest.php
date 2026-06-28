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
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class NsfwConfigControllerTest extends BaseApiWithDataTest
{
	public function testShowReturns501WhenNotConfigured(): void
	{
		config(['features.ai-vision-service.nsfw-url' => '']);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 501);
	}

	public function testShowProxiesConfigFromService(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		$actionCategory = ['labels' => ['FEMALE_GENITALIA_EXPOSED'], 'confidence' => 0.5, 'area_ratio' => 0.01, 'label_thresholds' => []];

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => Http::response([
				'config' => [
					'confidence_threshold' => '0.5',
					'area_ratio_threshold' => '0.01',
					'debug_detect_threshold' => '0.1',
					'block' => $actionCategory,
					'review' => $actionCategory,
					'sensitive' => $actionCategory,
					'queue_backend' => 'redis',
					'queue_max_size' => '100',
					'thread_pool_size' => '4',
					'verify_ssl' => 'true',
					'workers' => '2',
				],
				'presets' => [
					[
						'name' => 'default',
						'description' => 'Default preset',
						'block' => $actionCategory,
						'review' => $actionCategory,
						'sensitive' => $actionCategory,
					],
				],
			], 200),
		]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertOk($response);
		self::assertArrayHasKey('config', $response->json());
		self::assertArrayHasKey('presets', $response->json());
	}

	public function testShowReturns503WhenServiceErrors(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => Http::response([], 500),
		]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 503);
	}

	public function testShowReturns503WhenServiceUnreachable(): void
	{
		config(['features.ai-vision-service.nsfw-url' => 'http://fake-nsfw-service']);
		config(['features.ai-vision-service.nsfw-api-key' => 'test-key']);

		Http::fake([
			'http://fake-nsfw-service/api/nsfw/config' => function (): never {
				throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
			},
		]);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->getJson('NsfwDetection/config');

		$this->assertStatus($response, 503);
	}
}
