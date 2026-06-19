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

use App\Exceptions\ExternalComponentMissingException;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\AbstractTestCase;

class FacialRecognitionServiceTest extends AbstractTestCase
{
	private function makeService(string $url = 'http://ai-vision:8000', string $key = 'test-key'): FacialRecognitionService
	{
		Config::set('features.ai-vision-service.face-url', $url);
		Config::set('features.ai-vision-service.face-api-key', $key);

		return new FacialRecognitionService();
	}

	private function makeUnconfiguredService(): FacialRecognitionService
	{
		Config::set('features.ai-vision-service.face-url', '');
		Config::set('features.ai-vision-service.face-api-key', '');

		return new FacialRecognitionService();
	}

	// ── detectFaces ─────────────────────────────────────────────

	public function testDetectFacesPostsToService(): void
	{
		Http::fake([
			'ai-vision:8000/detect' => Http::response(['status' => 'ok'], 200),
		]);

		$service = $this->makeService();
		$response = $service->detectFaces('photo-123', 'uploads/original/photo.jpg');

		self::assertTrue($response->successful());
		Http::assertSent(fn ($request) => $request->url() === 'http://ai-vision:8000/detect' &&
			$request['photo_id'] === 'photo-123' &&
			$request['photo_path'] === 'uploads/original/photo.jpg' &&
			$request->hasHeader('X-API-Key', 'test-key'));
	}

	public function testDetectFacesThrowsWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();

		$this->expectException(ExternalComponentMissingException::class);
		$service->detectFaces('photo-123', 'uploads/original/photo.jpg');
	}

	// ── deleteEmbeddings ────────────────────────────────────────

	public function testDeleteEmbeddingsSendsDeleteRequest(): void
	{
		Http::fake([
			'ai-vision:8000/embeddings' => Http::response(['deleted' => 2], 200),
		]);

		$service = $this->makeService();
		$response = $service->deleteEmbeddings(['face-1', 'face-2']);

		self::assertTrue($response->successful());
		Http::assertSent(fn ($request) => $request->method() === 'DELETE' &&
			$request->url() === 'http://ai-vision:8000/embeddings' &&
			$request['face_ids'] === ['face-1', 'face-2'] &&
			$request->hasHeader('X-API-Key', 'test-key'));
	}

	public function testDeleteEmbeddingsThrowsWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();

		$this->expectException(ExternalComponentMissingException::class);
		$service->deleteEmbeddings(['face-1']);
	}

	// ── checkHealthRaw ──────────────────────────────────────────

	public function testCheckHealthRawReturnsResponse(): void
	{
		Http::fake([
			'ai-vision:8000/health' => Http::response(['status' => 'ok', 'model_loaded' => true, 'embedding_count' => 42], 200),
		]);

		$service = $this->makeService();
		$response = $service->checkHealthRaw();

		self::assertTrue($response->successful());
		self::assertEquals('ok', $response->json('status'));
		Http::assertSent(fn ($request) => $request->url() === 'http://ai-vision:8000/health' &&
			$request->hasHeader('X-API-Key', 'test-key'));
	}

	public function testCheckHealthRawThrowsWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();

		$this->expectException(ExternalComponentMissingException::class);
		$service->checkHealthRaw();
	}

	public function testCheckHealthRawRespectsTimeout(): void
	{
		Http::fake([
			'ai-vision:8000/health' => Http::response(['status' => 'ok'], 200),
		]);

		$service = $this->makeService();
		$response = $service->checkHealthRaw(10);

		self::assertTrue($response->successful());
	}

	// ── checkHealth ─────────────────────────────────────────────

	public function testCheckHealthReturnsArrayOnSuccess(): void
	{
		Http::fake([
			'ai-vision:8000/health' => Http::response([
				'status' => 'ok',
				'model_loaded' => true,
				'embedding_count' => 42,
			], 200),
		]);

		$service = $this->makeService();
		$result = $service->checkHealth();

		self::assertIsArray($result);
		self::assertEquals('ok', $result['status']);
		self::assertTrue($result['model_loaded']);
		self::assertEquals(42, $result['embedding_count']);
	}

	public function testCheckHealthReturnsNullOnFailure(): void
	{
		Http::fake([
			'ai-vision:8000/health' => Http::response(null, 500),
		]);

		$service = $this->makeService();
		$result = $service->checkHealth();

		self::assertNull($result);
	}

	public function testCheckHealthReturnsNullWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();
		$result = $service->checkHealth();

		self::assertNull($result);
	}

	public function testCheckHealthReturnsNullOnException(): void
	{
		Http::fake([
			'ai-vision:8000/health' => fn () => throw new \RuntimeException('Connection refused'),
		]);

		$service = $this->makeService();
		$result = $service->checkHealth();

		self::assertNull($result);
	}

	// ── getConfigurationRaw ─────────────────────────────────────

	public function testGetConfigurationRawReturnsResponse(): void
	{
		Http::fake([
			'ai-vision:8000/config' => Http::response([
				'config' => ['model_name' => 'ArcFace', 'api_key' => '***'],
			], 200),
		]);

		$service = $this->makeService();
		$response = $service->getConfigurationRaw();

		self::assertTrue($response->successful());
		Http::assertSent(fn ($request) => $request->url() === 'http://ai-vision:8000/config' &&
			$request->hasHeader('X-API-Key', 'test-key'));
	}

	public function testGetConfigurationRawThrowsWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();

		$this->expectException(ExternalComponentMissingException::class);
		$service->getConfigurationRaw();
	}

	// ── getConfiguration ────────────────────────────────────────

	public function testGetConfigurationReturnsArrayOnSuccess(): void
	{
		Http::fake([
			'ai-vision:8000/config' => Http::response([
				'config' => ['model_name' => 'ArcFace', 'api_key' => '***'],
			], 200),
		]);

		$service = $this->makeService();
		$result = $service->getConfiguration();

		self::assertIsArray($result);
		self::assertEquals('ArcFace', $result['model_name']);
		self::assertEquals('***', $result['api_key']);
	}

	public function testGetConfigurationReturnsNullOnFailure(): void
	{
		Http::fake([
			'ai-vision:8000/config' => Http::response(null, 500),
		]);

		$service = $this->makeService();
		$result = $service->getConfiguration();

		self::assertNull($result);
	}

	public function testGetConfigurationReturnsNullWhenNotConfigured(): void
	{
		$service = $this->makeUnconfiguredService();
		$result = $service->getConfiguration();

		self::assertNull($result);
	}

	public function testGetConfigurationReturnsNullOnInvalidPayload(): void
	{
		Http::fake([
			'ai-vision:8000/config' => Http::response(['unexpected' => 'data'], 200),
		]);

		$service = $this->makeService();
		$result = $service->getConfiguration();

		self::assertNull($result);
	}

	public function testGetConfigurationReturnsNullOnException(): void
	{
		Http::fake([
			'ai-vision:8000/config' => fn () => throw new \RuntimeException('timeout'),
		]);

		$service = $this->makeService();
		$result = $service->getConfiguration();

		self::assertNull($result);
	}

	public function testGetConfigurationFiltersNonStringKeys(): void
	{
		Http::fake([
			'ai-vision:8000/config' => Http::response([
				'config' => ['model_name' => 'ArcFace', 0 => 'ignored'],
			], 200),
		]);

		$service = $this->makeService();
		$result = $service->getConfiguration();

		self::assertIsArray($result);
		self::assertArrayHasKey('model_name', $result);
		self::assertArrayNotHasKey(0, $result);
	}
}
