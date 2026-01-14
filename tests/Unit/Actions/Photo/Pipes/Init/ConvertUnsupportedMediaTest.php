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

namespace Tests\Unit\Actions\Photo\Pipes\Init;

use App\Actions\Photo\Pipes\Init\ConvertUnsupportedMedia;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreate\InitDTO;
use App\Image\Files\NativeLocalFile;
use Tests\AbstractTestCase;

class ConvertUnsupportedMediaTest extends AbstractTestCase
{
	private ConvertUnsupportedMedia $pipe;

	protected function setUp(): void
	{
		parent::setUp();
		$this->pipe = new ConvertUnsupportedMedia();
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleWithJpegExtensionPassesThrough(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.jpg');

		$state = $this->createInitDTO($originalFile);

		$nextCalled = false;
		$next = function (InitDTO $state) use (&$nextCalled, $originalFile): InitDTO {
			$nextCalled = true;
			self::assertSame($originalFile, $state->source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($originalFile, $result->source_file);
	}

	public function testHandleWithPngExtensionPassesThrough(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.png');

		$state = $this->createInitDTO($originalFile);

		$nextCalled = false;
		$next = function (InitDTO $state) use (&$nextCalled, $originalFile): InitDTO {
			$nextCalled = true;
			self::assertSame($originalFile, $state->source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($originalFile, $result->source_file);
	}

	public function testHandleWithExtensionWithoutDotPassesThrough(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('jpg');

		$state = $this->createInitDTO($originalFile);

		$nextCalled = false;
		$next = function (InitDTO $state) use (&$nextCalled, $originalFile): InitDTO {
			$nextCalled = true;
			self::assertSame($originalFile, $state->source_file);

			return $state;
		};

		$result = $this->pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($originalFile, $result->source_file);
	}

	private function createInitDTO(NativeLocalFile $sourceFile): InitDTO
	{
		$importParam = new ImportParam(
			import_mode: new ImportMode(),
			intended_owner_id: 1,
		);

		return new InitDTO(
			parameters: $importParam,
			source_file: $sourceFile,
			album: null,
		);
	}
}
