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

use App\Actions\Photo\Pipes\Standalone\AutoScanNsfwOnUpload;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\UserUploadTrustLevel;
use App\Image\Files\NativeLocalFile;
use App\Jobs\DispatchNsfwScanJob;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Queue;
use Tests\AbstractTestCase;

class AutoScanNsfwOnUploadTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	// ── DISABLED: AI VISION OFF ─────────────────────────────────

	public function testDoesNotScanWhenAiVisionDisabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertNotPushed(DispatchNsfwScanJob::class);
	}

	// ── DISABLED: NSFW OFF ──────────────────────────────────────

	public function testDoesNotScanWhenNsfwDisabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertNotPushed(DispatchNsfwScanJob::class);
	}

	// ── TRUSTED USER: SCAN DISABLED ─────────────────────────────

	public function testDoesNotScanTrustedUserWhenConfigDisabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_scan_trusted_users')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::TRUSTED);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertNotPushed(DispatchNsfwScanJob::class);
	}

	// ── TRUSTED USER: SCAN ENABLED ──────────────────────────────

	public function testScansTrustedUserWhenConfigEnabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_scan_trusted_users')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_trust_hide_on_scan')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::TRUSTED, is_photo: true);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertPushed(DispatchNsfwScanJob::class);
	}

	// ── MONITOR USER: DISPATCHES ────────────────────────────────

	public function testScansMonitorUser(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_monitor_hide_on_scan')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR, is_photo: true);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertPushed(DispatchNsfwScanJob::class);
	}

	// ── HIDE ON SCAN ────────────────────────────────────────────

	public function testHideOnScanSetsIsValidatedFalse(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_monitor_hide_on_scan')
			->andReturn('1');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR, is_photo: true);

		$pipe->handle($state, function ($s) {
			self::assertFalse($s->photo->is_validated);

			return $s;
		});
	}

	public function testNoHideOnScanWhenConfigDisabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_monitor_hide_on_scan')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR, is_photo: true);
		$state->photo->is_validated = true;

		$pipe->handle($state, function ($s) {
			self::assertTrue($s->photo->is_validated);

			return $s;
		});
	}

	// ── SKIPS NON-PHOTO ─────────────────────────────────────────

	public function testSkipsNonPhotoFiles(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_enabled')
			->andReturn('1');
		$config_manager->shouldReceive('getValueAsString')
			->with('ai_vision_nsfw_monitor_hide_on_scan')
			->andReturn('0');

		$pipe = new AutoScanNsfwOnUpload($config_manager);
		$state = $this->createDto(UserUploadTrustLevel::MONITOR, is_photo: false);

		$pipe->handle($state, fn ($s) => $s);

		Queue::assertNotPushed(DispatchNsfwScanJob::class);
	}

	private function createDto(UserUploadTrustLevel $trust_level, bool $is_photo = false): StandaloneDTO
	{
		$source_file = \Mockery::mock(NativeLocalFile::class);
		$exif_info = \Mockery::mock(Extractor::class);

		$attrs = ['is_validated' => true, 'id' => 'test-photo-id'];
		$photo = \Mockery::mock(Photo::class)->makePartial();
		$photo->shouldReceive('isPhoto')->andReturn($is_photo);
		$photo->shouldReceive('setAttribute')->andReturnUsing(function ($key, $value) use (&$attrs) {
			$attrs[$key] = $value;
		});
		$photo->shouldReceive('getAttribute')->andReturnUsing(function ($key) use (&$attrs) {
			return $attrs[$key] ?? null;
		});
		$photo->shouldReceive('__get')->andReturnUsing(function ($key) use (&$attrs) {
			return $attrs[$key] ?? null;
		});
		$photo->shouldReceive('__set')->andReturnUsing(function ($key, $value) use (&$attrs) {
			$attrs[$key] = $value;
		});

		return new StandaloneDTO(
			photo: $photo,
			source_file: $source_file,
			is_highlighted: false,
			exif_info: $exif_info,
			album: null,
			intended_owner_id: 1,
			upload_trust_level: $trust_level,
			shall_import_via_symlink: false,
			shall_delete_imported: false,
			shall_rename_photo_title: false,
			apply_watermark: null,
		);
	}
}
