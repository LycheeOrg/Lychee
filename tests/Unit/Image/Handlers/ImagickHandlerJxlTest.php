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
use App\Image\Handlers\ImagickHandler;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * Imagick without the JPEG XL delegate (Feature 101, FR-101-06, S-101-07).
 */
class ImagickHandlerJxlTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected function setUp(): void
	{
		parent::setUp();
		if (!extension_loaded('imagick')) {
			self::markTestSkipped('Imagick is not installed.');
		}
	}

	public function testNoJxlWithoutImagick(): void
	{
		Configs::set('imagick', '0');

		self::assertFalse(resolve(ConfigManager::class)->hasJxl());
	}

	public function testSavingJxlFailsClearlyWithoutSupport(): void
	{
		$handler = $this->handlerWithoutJxl();
		$handler->load(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_PNG)));
		$target = new TemporaryLocalFile('.jxl');

		try {
			$handler->save($target);
			self::fail('Imagick without JPEG XL support must not write a .jxl file');
		} catch (MediaFileOperationException $e) {
			self::assertSame(BaseImageHandler::JXL_UNSUPPORTED, $e->getMessage());
			self::assertSame('', file_get_contents($target->getRealPath()));
		} finally {
			$target->delete();
		}
	}

	public function testLoadingJxlFailsClearlyWithoutSupport(): void
	{
		$source = new TemporaryLocalFile('.jxl');
		file_put_contents($source->getRealPath(), FileExtensionService::JXL_SIGNATURES[0]);

		try {
			$this->expectException(MediaFileOperationException::class);
			$this->expectExceptionMessage(BaseImageHandler::JXL_UNSUPPORTED);
			$this->handlerWithoutJxl()->load($source);
		} finally {
			$source->delete();
		}
	}

	private function handlerWithoutJxl(): ImagickHandler
	{
		return new class() extends ImagickHandler {
			protected function supportsJxl(): bool
			{
				return false;
			}
		};
	}
}
