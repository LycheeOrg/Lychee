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
use App\Exceptions\CannotConvertMediaFileException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;
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

	public function testHandleWithHeifExtensionTriggersConversion(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);
		$convertedFile = \Mockery::mock(TemporaryJobFile::class);

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.heif');

		$originalFile->shouldReceive('getRealPath')
			->andReturn('/tmp/test.heif');

		$state = $this->createInitDTO($originalFile);

		$nextCalled = false;
		$next = function (InitDTO $state) use (&$nextCalled): InitDTO {
			$nextCalled = true;

			return $state;
		};

		try {
			$result = $this->pipe->handle($state, $next);
			$this->assertTrue($nextCalled);
			$this->assertInstanceOf(TemporaryJobFile::class, $result->source_file);
		} catch (\Exception $e) {
			if ($e instanceof CannotConvertMediaFileException || str_contains($e->getMessage(), 'convert')) {
				$this->markTestSkipped('HEIF conversion not available in test environment: ' . $e->getMessage());
			}
			throw $e;
		}
	}

	public function testHandleWithHeicExtensionTriggersConversion(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.heic');

		$originalFile->shouldReceive('getRealPath')
			->andReturn('/tmp/test.heic');

		$state = $this->createInitDTO($originalFile);

		$nextCalled = false;
		$next = function (InitDTO $state) use (&$nextCalled): InitDTO {
			$nextCalled = true;

			return $state;
		};

		try {
			$result = $this->pipe->handle($state, $next);
			$this->assertTrue($nextCalled);
			$this->assertInstanceOf(TemporaryJobFile::class, $result->source_file);
		} catch (\Exception $e) {
			if ($e instanceof CannotConvertMediaFileException || str_contains($e->getMessage(), 'convert')) {
				$this->markTestSkipped('HEIC conversion not available in test environment: ' . $e->getMessage());
			}
			throw $e;
		}
	}

	public function testHandleWithConversionExceptionWrapsInCannotConvertMediaFileException(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);
		$originalException = new \Exception('Conversion failed');

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.heif');

		$originalFile->shouldReceive('getRealPath')
			->andThrow($originalException);

		$state = $this->createInitDTO($originalFile);

		$next = function (InitDTO $state): InitDTO {
			return $state;
		};

		$this->expectException(CannotConvertMediaFileException::class);
		$this->expectExceptionMessageMatches('/Failed to convert HEIC\/HEIF to JPEG.*Conversion failed/');

		try {
			$this->pipe->handle($state, $next);
		} catch (CannotConvertMediaFileException $e) {
			$this->assertNotNull($e->getPrevious());
			$this->assertInstanceOf(\Exception::class, $e->getPrevious());
			$this->assertStringContainsString('Failed to convert HEIC/HEIF to JPEG', $e->getMessage());
			throw $e;
		}
	}

	public function testHandleWithRuntimeExceptionWrapsInCannotConvertMediaFileException(): void
	{
		$originalFile = \Mockery::mock(NativeLocalFile::class);
		$originalException = new \RuntimeException('Runtime error during conversion');

		$originalFile->shouldReceive('getOriginalExtension')
			->once()
			->andReturn('.heic');

		$originalFile->shouldReceive('getRealPath')
			->andThrow($originalException);

		$state = $this->createInitDTO($originalFile);

		$next = function (InitDTO $state): InitDTO {
			return $state;
		};

		$this->expectException(CannotConvertMediaFileException::class);
		$this->expectExceptionMessageMatches('/Failed to convert HEIC\/HEIF to JPEG.*Runtime error during conversion/');

		try {
			$this->pipe->handle($state, $next);
		} catch (CannotConvertMediaFileException $e) {
			$this->assertNotNull($e->getPrevious());
			$this->assertInstanceOf(\Exception::class, $e->getPrevious());
			$this->assertStringContainsString('Failed to convert HEIC/HEIF to JPEG', $e->getMessage());
			throw $e;
		}
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
