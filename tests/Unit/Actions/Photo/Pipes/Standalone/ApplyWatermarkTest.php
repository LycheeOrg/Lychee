<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Unit\Actions\Photo\Pipes\Standalone;

use App\Actions\Photo\Pipes\Standalone\ApplyWatermark;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Image\Files\NativeLocalFile;
use App\Image\Watermarker;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Tests\AbstractTestCase;

class ApplyWatermarkTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testSkipsWhenUserOptedOut(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$watermarker = \Mockery::mock(Watermarker::class);

		// Config should never be checked because apply_watermark = false
		$config_manager->shouldNotReceive('getValueAsBool');
		$watermarker->shouldNotReceive('can_watermark');
		$watermarker->shouldNotReceive('do');

		$pipe = new ApplyWatermark($config_manager, $watermarker);

		$state = $this->createStandaloneDTO(apply_watermark: false);

		$nextCalled = false;
		$next = function (StandaloneDTO $state) use (&$nextCalled): StandaloneDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
	}

	public function testAppliesWhenUserExplicitlyEnabled(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$watermarker = \Mockery::mock(Watermarker::class);

		// Global setting is checked
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('watermark_enabled')
			->andReturn(true);

		$watermarker->shouldReceive('can_watermark')
			->once()
			->andReturn(true);

		// No size variants, so watermarker->do should not be called
		$watermarker->shouldNotReceive('do');

		$pipe = new ApplyWatermark($config_manager, $watermarker);

		$state = $this->createStandaloneDTO(apply_watermark: true);

		$nextCalled = false;
		$next = function (StandaloneDTO $state) use (&$nextCalled): StandaloneDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
	}

	public function testUsesGlobalSettingWhenNull(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$watermarker = \Mockery::mock(Watermarker::class);

		// Global setting is checked
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('watermark_enabled')
			->andReturn(true);

		$watermarker->shouldReceive('can_watermark')
			->once()
			->andReturn(true);

		// No size variants, so watermarker->do should not be called
		$watermarker->shouldNotReceive('do');

		$pipe = new ApplyWatermark($config_manager, $watermarker);

		$state = $this->createStandaloneDTO(apply_watermark: null);

		$nextCalled = false;
		$next = function (StandaloneDTO $state) use (&$nextCalled): StandaloneDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
	}

	public function testSkipsWhenGloballyDisabled(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$watermarker = \Mockery::mock(Watermarker::class);

		// Global setting is checked and returns false
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('watermark_enabled')
			->andReturn(false);

		$watermarker->shouldNotReceive('can_watermark');
		$watermarker->shouldNotReceive('do');

		$pipe = new ApplyWatermark($config_manager, $watermarker);

		$state = $this->createStandaloneDTO(apply_watermark: null);

		$nextCalled = false;
		$next = function (StandaloneDTO $state) use (&$nextCalled): StandaloneDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
	}

	private function createStandaloneDTO(?bool $apply_watermark): StandaloneDTO
	{
		$source_file = \Mockery::mock(NativeLocalFile::class);
		$exif_info = \Mockery::mock(Extractor::class);
		$photo = \Mockery::mock(Photo::class);

		// Mock the size_variants relation to return an empty collection
		$photo->shouldReceive('getAttribute')
			->with('size_variants')
			->andReturn(\Mockery::mock([
				'toCollection' => \Illuminate\Support\Collection::make([]),
			]));

		return new StandaloneDTO(
			photo: $photo,
			source_file: $source_file,
			is_highlighted: false,
			exif_info: $exif_info,
			album: null,
			intended_owner_id: 1,
			shall_import_via_symlink: false,
			shall_delete_imported: false,
			shall_rename_photo_title: false,
			apply_watermark: $apply_watermark,
		);
	}
}
