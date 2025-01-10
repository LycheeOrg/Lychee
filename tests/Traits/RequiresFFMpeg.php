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

namespace Tests\Traits;

use App\Models\Configs;
use Tests\Constants\TestConstants;

trait RequiresFFMpeg
{
	protected bool $hasFFMpeg;
	protected int $hasFFMpegInit;

	protected function setUpRequiresFFMpeg(): void
	{
		$this->hasFFMpegInit = Configs::getValueAsInt(TestConstants::CONFIG_HAS_FFMPEG);
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, 2);
		$this->hasFFMpeg = Configs::hasFFmpeg();
	}

	protected function tearDownRequiresFFMpeg(): void
	{
		Configs::set(TestConstants::CONFIG_HAS_FFMPEG, $this->hasFFMpegInit);
	}

	protected function assertHasFFMpegOrSkip(): void
	{
		if (!$this->hasFFMpeg) {
			static::markTestSkipped('FFMpeg is not available. Test Skipped.');
		}
	}

	abstract public static function markTestSkipped(string $message = ''): void;
}
