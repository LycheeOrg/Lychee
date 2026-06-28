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

use App\Actions\Diagnostics\Pipes\Checks\AiVisionNsfwServiceCheck;
use App\Enum\MessageType;
use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\ExternalComponentMissingException;
use App\Repositories\ConfigManager;
use App\Services\Image\NsfwDetectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\AbstractTestCase;

class AiVisionNsfwServiceCheckTest extends AbstractTestCase
{
	/** @var ConfigManager&MockObject */
	private ConfigManager $config_manager;
	/** @var NsfwDetectionService&MockObject */
	private NsfwDetectionService $nsfw_detection_service;
	private AiVisionNsfwServiceCheck $check;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config_manager = $this->createMock(ConfigManager::class);
		$this->nsfw_detection_service = $this->createMock(NsfwDetectionService::class);
		$this->check = new AiVisionNsfwServiceCheck($this->config_manager, $this->nsfw_detection_service);
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

	public function testSkipsWhenNsfwDisabled(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', false],
		]);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	// ── not configured ──────────────────────────────────────────

	public function testErrorWhenServiceNotConfigured(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(false);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willThrowException(new ExternalComponentMissingException('NSFW classification service is not configured.'));

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
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willThrowException(new ExternalComponentFailedException('NSFW classification service health check failed with status 503.'));

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('503', $result[0]->message);
	}

	public function testErrorWhenHealthCheckReturnsInvalidFormat(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willThrowException(new ExternalComponentFailedException('NSFW classification service health endpoint returned invalid response format.'));

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('invalid response format', $result[0]->message);
	}

	public function testWarnWhenHealthCheckReturnsUnhealthyStatus(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willReturn(['status' => 'degraded']);

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
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willReturn(['status' => 'ok']);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	public function testNoErrorsWhenHealthCheckReturnsHealthy(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willReturn(['status' => 'healthy']);

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertSame([], $result);
	}

	// ── connection errors ───────────────────────────────────────

	public function testErrorWhenConnectionFails(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		$this->config_manager->method('getValueAsBool')->willReturnMap([
			['ai_vision_enabled', true],
			['ai_vision_nsfw_enabled', true],
		]);
		$this->nsfw_detection_service->method('isConfigured')->willReturn(true);
		Auth::shouldReceive('user')->andReturn((object) ['may_administrate' => true]);

		$this->nsfw_detection_service->method('checkHealth')
			->willThrowException(new ExternalComponentFailedException('Could not connect to NSFW classification service.'));

		$data = [];
		$result = $this->check->handle($data, $this->passThrough());

		self::assertCount(1, $result);
		self::assertEquals(MessageType::ERROR, $result[0]->type);
		self::assertStringContainsString('Could not connect', $result[0]->message);
	}
}
