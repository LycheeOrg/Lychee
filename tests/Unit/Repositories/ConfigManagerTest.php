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

namespace Tests\Unit\Repositories;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Tests\AbstractTestCase;

class ConfigManagerTest extends AbstractTestCase
{
	public function tearDown(): void
	{
		// AbstractTestCase does not wrap tests in a DB transaction, so restore defaults explicitly.
		Configs::set('zip_bomb_max_file_size', '5GB');
		Configs::set('upload_chunk_size', '0 B');

		parent::tearDown();
	}

	public function testGetValueAsByteSizeDefaults(): void
	{
		$config_manager = resolve(ConfigManager::class);

		self::assertEquals(10 * 1024 * 1024 * 1024, $config_manager->getValueAsByteSize('zip_bomb_max_total_size'));
		self::assertEquals(5 * 1024 * 1024 * 1024, $config_manager->getValueAsByteSize('zip_bomb_max_file_size'));
	}

	public function testGetValueAsByteSizeCustomValue(): void
	{
		Configs::set('zip_bomb_max_file_size', '512MB');

		$config_manager = resolve(ConfigManager::class);
		$config_manager->invalidateCache();

		self::assertEquals(512 * 1024 * 1024, $config_manager->getValueAsByteSize('zip_bomb_max_file_size'));
	}

	public function testGetValueAsByteSizeZeroSentinel(): void
	{
		Configs::set('upload_chunk_size', '0 B');

		$config_manager = resolve(ConfigManager::class);
		$config_manager->invalidateCache();

		self::assertEquals(0, $config_manager->getValueAsByteSize('upload_chunk_size'));
	}
}
