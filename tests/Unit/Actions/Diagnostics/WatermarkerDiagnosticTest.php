<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\WatermarkerEnabledCheck;
use App\DTO\DiagnosticDTO;
use App\Enum\MessageType;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\AbstractTestCase;
use Tests\Constants\FreeVerifyier;
use Tests\Traits\RequireSE;

class WatermarkerDiagnosticTest extends AbstractTestCase
{
	use RequireSE;
	use DatabaseTransactions;

	private WatermarkerEnabledCheck $watermarkerCheck;
	private DiagnosticDTO $data;
	private \Closure $next;

	protected function setUp(): void
	{
		parent::setUp();
		$this->watermarkerCheck = new WatermarkerEnabledCheck();
		$verify = new FreeVerifyier();
		$configManager = app(ConfigManager::class);
		$this->data = new DiagnosticDTO($verify, $configManager, []);
		$this->next = function (DiagnosticDTO $data) {
			return $data;
		};

		$this->requireSe();
	}

	protected function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testHandleWhenTablesDoNotExist(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(false);
		Schema::shouldReceive('hasTable')->with('size_variants')->andReturn(false);

		$result = $this->watermarkerCheck->handle($this->data, $this->next);

		$this->assertEmpty($result->data, 'Should return empty result when tables do not exist');
	}

	public function testHandleWhenWatermarkDisabled(): void
	{
		Schema::shouldReceive('hasTable')->with('configs')->andReturn(true);
		Schema::shouldReceive('hasTable')->with('size_variants')->andReturn(true);
		Configs::set('watermark_enabled', false);

		$result = $this->watermarkerCheck->handle($this->data, $this->next);

		$this->assertEmpty($result->data, 'Should return empty result when watermark is disabled');
	}

	public function testValidateImagickWhenImagickDisabledInSettings(): void
	{
		Configs::set('watermark_enabled', true);
		Configs::set('imagick', false);
		Configs::set('watermark_photo_id', '');

		$result = $this->watermarkerCheck->handle($this->data, $this->next);

		$this->assertCount(2, $result->data);
		$this->assertEquals('Watermarker: imagick is not enabled in your settings. Watermarking step will be skipped.', $result->data[0]->message);
		$this->assertEquals(MessageType::WARNING, $result->data[0]->type);
		$this->assertEquals('Watermarker: photo_id is not provided. Watermarking step will be skipped.', $result->data[1]->message);
		$this->assertEquals(MessageType::WARNING, $result->data[1]->type);
	}

	public function testValidateImagickWhenImagickEnabledInSettings(): void
	{
		Configs::set('watermark_enabled', true);
		Configs::set('imagick', true);
		Configs::set('watermark_photo_id', 'some-id');

		$result = $this->watermarkerCheck->handle($this->data, $this->next);

		$this->assertCount(1, $result->data);
		$this->assertEquals('Watermarker: the photo_id provided does not match any photo. Watermarking step will be skipped.', $result->data[0]->message);
		$this->assertEquals(MessageType::ERROR, $result->data[0]->type);
	}
}
