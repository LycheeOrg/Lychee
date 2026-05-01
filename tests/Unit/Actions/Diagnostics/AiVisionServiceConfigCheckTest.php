<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\AiVisionServiceConfigCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Repositories\ConfigManager;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\AbstractTestCase;

class AiVisionServiceConfigCheckTest extends AbstractTestCase
{
	/** @var ConfigManager&MockObject */
	private ConfigManager $config_manager;
	/** @var FacialRecognitionService&MockObject */
	private FacialRecognitionService $facial_recognition_service;
	private AiVisionServiceConfigCheck $check;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config_manager = $this->createMock(ConfigManager::class);
		$this->facial_recognition_service = $this->createMock(FacialRecognitionService::class);
		$this->check = new AiVisionServiceConfigCheck($this->config_manager, $this->facial_recognition_service);
	}

	/**
	 * @return void
	 */
	public function testHandleSkipsWhenDebugDisabled(): void
	{
		Config::set('app.debug', false);

		$data = [];
		$result = $this->check->handle($data, fn (array $diagnostics): array => $diagnostics);

		$this->assertSame([], $result);
	}

	/**
	 * @return void
	 */
	public function testHandleSkipsWhenAiVisionDisabled(): void
	{
		Config::set('app.debug', true);
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);

		$this->config_manager->expects($this->once())
			->method('getValueAsBool')
			->with('ai_vision_enabled')
			->willReturn(false);

		$data = [];
		$result = $this->check->handle($data, fn (array $diagnostics): array => $diagnostics);

		$this->assertSame([], $result);
	}

	/**
	 * @return void
	 */
	public function testHandleAddsInfoWhenConfigurationIsAvailable(): void
	{
		Config::set('app.debug', true);
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);

		$this->config_manager->expects($this->once())
			->method('getValueAsBool')
			->with('ai_vision_enabled')
			->willReturn(true);
		$this->facial_recognition_service->expects($this->once())
			->method('isConfigured')
			->willReturn(true);
		$this->facial_recognition_service->expects($this->once())
			->method('getConfiguration')
			->willReturn([
				'api_key' => '***',
				'model_name' => 'ArcFace',
			]);

		$data = [];
		$result = $this->check->handle($data, fn (array $diagnostics): array => $diagnostics);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::INFO, $result[0]->type);
		$this->assertEquals('AI Vision: Runtime configuration from service (debug mode).', $result[0]->message);
		$this->assertContains('api_key: ***', $result[0]->details);
		$this->assertContains('model_name: ArcFace', $result[0]->details);
	}

	/**
	 * @return void
	 */
	public function testHandleAddsWarningWhenConfigurationQueryFails(): void
	{
		Config::set('app.debug', true);
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);

		$this->config_manager->expects($this->once())
			->method('getValueAsBool')
			->with('ai_vision_enabled')
			->willReturn(true);
		$this->facial_recognition_service->expects($this->once())
			->method('isConfigured')
			->willReturn(true);
		$this->facial_recognition_service->expects($this->once())
			->method('getConfiguration')
			->willReturn(null);

		$data = [];
		$result = $this->check->handle($data, fn (array $diagnostics): array => $diagnostics);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::WARNING, $result[0]->type);
		$this->assertEquals(
			'AI Vision: Could not fetch runtime configuration from the service while APP_DEBUG is enabled.',
			$result[0]->message
		);
	}
}
