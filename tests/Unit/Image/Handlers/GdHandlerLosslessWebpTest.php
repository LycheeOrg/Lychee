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
use App\Image\Handlers\GdHandler;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * Lossless WebP on GD builds whose libgd lacks `gdWebpLossless` (Q-071-07).
 */
class GdHandlerLosslessWebpTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testLosslessWebpFailsClearlyWithoutGdSupport(): void
	{
		Configs::set('compression_quality', 0);
		$handler = new class() extends GdHandler {
			public static function supportsLosslessWebp(): bool
			{
				return false;
			}
		};
		$handler->load(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_PNG)));
		$target = new TemporaryLocalFile('.webp');

		try {
			$this->expectException(MediaFileOperationException::class);
			$this->expectExceptionMessage('Lossless WebP encoding is not supported by this GD build');
			$handler->save($target);
		} finally {
			$target->delete();
		}
	}

	public function testLossyWebpDoesNotNeedLosslessSupport(): void
	{
		Configs::set('compression_quality', 80);
		$handler = new class() extends GdHandler {
			public static function supportsLosslessWebp(): bool
			{
				return false;
			}
		};
		$handler->load(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_PNG)));
		$target = new TemporaryLocalFile('.webp');

		try {
			$handler->save($target);
			self::assertEquals('WEBP', substr(\Safe\file_get_contents($target->getRealPath()), 8, 4));
		} finally {
			$target->delete();
		}
	}
}
