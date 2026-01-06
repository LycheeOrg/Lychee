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

namespace Tests\Traits;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use Tests\Constants\TestConstants;

trait InteractsWithRaw
{
	public static function getAcceptedRawFormats(): string
	{
		$config_manager = resolve(ConfigManager::class);

		return $config_manager->getValueAsString(TestConstants::CONFIG_RAW_FORMATS);
	}

	public static function setAcceptedRawFormats(string $acceptedRawFormats): void
	{
		Configs::set(TestConstants::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		$file_extension_service = resolve(FileExtensionService::class);
		$reflection = new \ReflectionClass($file_extension_service);
		$property = $reflection->getProperty('cached_accepted_raw_file_extensions');
		$property->setValue($file_extension_service, []);
	}
}
