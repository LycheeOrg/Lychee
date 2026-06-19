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

use App\Actions\Diagnostics\Pipes\Checks\AiVisionServiceCheck;
use App\Enum\MessageType;
use App\Repositories\ConfigManager;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\AbstractTestCase;

class AiVisionServiceCheckTest extends AbstractTestCase
{
	/** @var ConfigManager&MockObject */
	private ConfigManager $config_manager;
	/** @var FacialRecognitionService&MockObject */
	private FacialRecognitionService $facial_recognition_service;
	private AiVisionServiceCheck $check;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config_manager = $this->createMock(ConfigManager::class);
		$this->facial_recognition_service = $this->createMock(FacialRecognitionService::class);
		$this->check = new AiVisionServiceCheck($this->config_manager, $this->facial_recognition_service);
	}

	private function passThrough(): \Closure
	{
		return fn (array $diagnostics): array => $diagnostics;
	}

	// ── skip conditions ─────────────────────────────────────────

	public function testSkipsWhenConfigsTableMissing(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(false);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	public function testSkipsWhenAiVisionDisabled(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(false);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	// ── not configured ──────────────────────────────────────────

	public function testErrorWhenServiceNotConfigured(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(false);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('not configured', $result[0]->message);
	}

	// ── health check failures ───────────────────────────────────

	public function testErrorWhenHealthCheckReturnsNonSuccessStatus(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(false);
		$response->method('status')->willReturn(503);

		$this->facial_recognition_service->method('checkHealthRaw')->willReturn($response);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('503', $result[0]->message);
	}

	public function testErrorWhenHealthCheckReturnsInvalidFormat(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);
		$response->method('json')->willReturn(['unexpected' => 'data']);
		$response->method('body')->willReturn('{"unexpected":"data"}');

		$this->facial_recognition_service->method('checkHealthRaw')->willReturn($response);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('invalid response format', $result[0]->message);
	}

	public function testWarnWhenHealthCheckReturnsUnhealthyStatus(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);
		$response->method('json')->willReturn(['status' => 'degraded']);

		$this->facial_recognition_service->method('checkHealthRaw')->willReturn($response);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertStringContainsString('degraded', $result[0]->message);
	}

	// ── health check success ────────────────────────────────────

	public function testNoErrorsWhenHealthCheckReturnsOk(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);
		$response->method('json')->willReturn(['status' => 'ok']);

		$this->facial_recognition_service->method('checkHealthRaw')->willReturn($response);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	public function testNoErrorsWhenHealthCheckReturnsHealthy(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);
		$response->method('json')->willReturn(['status' => 'healthy']);

		$this->facial_recognition_service->method('checkHealthRaw')->willReturn($response);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	// ── connection errors ───────────────────────────────────────

	public function testErrorWhenConnectionFails(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->with('ai_vision_enabled')->willReturn(true);
		$this->facial_recognition_service->method('isConfigured')->willReturn(true);

		Config::set('features.ai-vision-service.face-url', 'http://ai-vision:8000');

		$this->facial_recognition_service->method('checkHealthRaw')
			->willThrowException(new ConnectionException('Connection refused'));

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('Could not connect', $result[0]->message);
	}
}
