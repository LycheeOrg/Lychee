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

namespace Tests\Unit\Actions\Photo\Pipes\Init;

use App\Actions\Photo\Pipes\Init\DetectAndStoreRaw;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreate\InitDTO;
use App\Image\Files\NativeLocalFile;
use App\Services\Image\FileExtensionService;
use Tests\AbstractTestCase;

class DetectAndStoreRawTest extends AbstractTestCase
{
	private DetectAndStoreRaw $pipe;

	protected function setUp(): void
	{
		parent::setUp();
		$this->pipe = resolve(DetectAndStoreRaw::class);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleWithJpegExtensionPassesThrough(): void
	{
		$original_file = \Mockery::mock(NativeLocalFile::class);

		$original_file->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.jpg');

		$state = $this->createInitDTO($original_file);

		$next_called = false;
		$next = function (InitDTO $state) use (&$next_called, $original_file): InitDTO {
			$next_called = true;
			self::assertSame($original_file, $state->source_file);
			self::assertNull($state->raw_source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($next_called);
		$this->assertSame($original_file, $result->source_file);
		$this->assertNull($result->raw_source_file);
	}

	public function testHandleWithPngExtensionPassesThrough(): void
	{
		$original_file = \Mockery::mock(NativeLocalFile::class);

		$original_file->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.png');

		$state = $this->createInitDTO($original_file);

		$next_called = false;
		$next = function (InitDTO $state) use (&$next_called, $original_file): InitDTO {
			$next_called = true;
			self::assertSame($original_file, $state->source_file);
			self::assertNull($state->raw_source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($next_called);
		$this->assertSame($original_file, $result->source_file);
		$this->assertNull($result->raw_source_file);
	}

	public function testHandleWithPdfExtensionPassesThrough(): void
	{
		$original_file = \Mockery::mock(NativeLocalFile::class);

		$original_file->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.pdf');

		$state = $this->createInitDTO($original_file);

		$next_called = false;
		$next = function (InitDTO $state) use (&$next_called, $original_file): InitDTO {
			$next_called = true;
			// PDF is NOT a convertible RAW format — passes through unmodified
			self::assertSame($original_file, $state->source_file);
			self::assertNull($state->raw_source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($next_called);
		$this->assertSame($original_file, $result->source_file);
		$this->assertNull($result->raw_source_file);
	}

	public function testConvertibleExtensionsList(): void
	{
		// Verify all documented convertible extensions are in the constant
		$expected = [
			'.nef', '.cr2', '.cr3', '.arw', '.dng', '.orf',
			'.rw2', '.raf', '.pef', '.srw', '.nrw', '.psd',
			'.heic', '.heif',
		];

		self::assertSame($expected, FileExtensionService::CONVERTIBLE_RAW_EXTENSIONS);
	}

	private function createInitDTO(NativeLocalFile $source_file): InitDTO
	{
		$import_param = new ImportParam(
			import_mode: new ImportMode(),
			intended_owner_id: 1,
		);

		return new InitDTO(
			parameters: $import_param,
			source_file: $source_file,
			album: null,
		);
	}
}
