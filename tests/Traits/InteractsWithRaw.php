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

use App\Image\Files\BaseMediaFile;
use App\Models\Configs;
use Tests\Constants\TestConstants;

trait InteractsWithRaw
{
	public static function getAcceptedRawFormats(): string
	{
		return Configs::getValueAsString(TestConstants::CONFIG_RAW_FORMATS);
	}

	public static function setAcceptedRawFormats(string $acceptedRawFormats): void
	{
		Configs::set(TestConstants::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		$reflection = new \ReflectionClass(BaseMediaFile::class);
		$reflection->setStaticPropertyValue('cachedAcceptedRawFileExtensions', []);
	}
}
