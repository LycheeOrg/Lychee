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

namespace Tests\Unit\Image\Handlers;

use App\Exceptions\MediaFileOperationException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\BaseImageHandler;
use App\Image\Handlers\GdHandler;
use App\Services\Image\FileExtensionService;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * GD has no JPEG XL support (Feature 101, FR-101-06, S-101-08).
 */
class GdHandlerJxlTest extends AbstractTestCase
{
	public function testSavingJxlFailsClearly(): void
	{
		$handler = new GdHandler();
		$handler->load(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_PNG)));
		$target = new TemporaryLocalFile('.jxl');

		try {
			$handler->save($target);
			self::fail('GD must not write a .jxl file');
		} catch (MediaFileOperationException $e) {
			self::assertSame(BaseImageHandler::JXL_UNSUPPORTED, $e->getMessage());
			// Nothing was written: no non-JXL bytes behind a .jxl extension.
			self::assertSame('', file_get_contents($target->getRealPath()));
		} finally {
			$target->delete();
		}
	}

	public function testLoadingJxlFailsClearly(): void
	{
		$source = new TemporaryLocalFile('.jxl');
		file_put_contents($source->getRealPath(), FileExtensionService::JXL_SIGNATURES[0]);

		try {
			$this->expectException(MediaFileOperationException::class);
			$this->expectExceptionMessage(BaseImageHandler::JXL_UNSUPPORTED);
			(new GdHandler())->load($source);
		} finally {
			$source->delete();
		}
	}
}
