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

namespace Tests\Feature_v2\Admin;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ConfigTogglePresentTest extends BaseApiWithDataTest
{
	public function testRowExistsForUseAdminDashboard(): void
	{
		$config = Configs::where('key', 'use_admin_dashboard')->first();
		$this->assertNotNull($config);
	}

	public function testDefaultValueIsOne(): void
	{
		$config = Configs::where('key', 'use_admin_dashboard')->first();
		$this->assertNotNull($config);
		$this->assertSame('1', $config->value);
	}

	public function testCategoryIsConfig(): void
	{
		$config = Configs::where('key', 'use_admin_dashboard')->first();
		$this->assertNotNull($config);
		$this->assertSame('config', $config->cat);
	}

	public function testGetValueAsBoolReturnsTrue(): void
	{
		/** @var \App\Repositories\ConfigManager $config_manager */
		$config_manager = app(\App\Repositories\ConfigManager::class);
		$result = $config_manager->getValueAsBool('use_admin_dashboard');
		$this->assertTrue($result);
	}
}
