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

namespace Tests\Unit\Models;

use App\Enum\ConfigType;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Models\Configs;
use Tests\AbstractTestCase;

class ConfigsTest extends AbstractTestCase
{
	public function testSetFailing(): void
	{
		self::expectException(InvalidConfigOption::class);
		Configs::set('default_license', 'something');
	}

	public function testSanity(): void
	{
		$config = new Configs(
			[
				'key' => 'key',
				'value' => '',
				'type_range' => ConfigType::STRING_REQ->value,
			]
		);
		self::assertEquals('Error: key empty or not set', $config->sanity(''));
		self::assertEquals('Error: key empty or not set', $config->sanity(null));

		$config->type_range = ConfigType::POSTIIVE->value;
		self::assertEquals('Error: Wrong property for key, expected strictly positive integer, got a.', $config->sanity('a'));
		self::assertEquals('Error: Wrong property for key, expected strictly positive integer, got -1.', $config->sanity('-1'));
		self::assertEquals('Error: Wrong property for key, expected strictly positive integer, got 0.', $config->sanity('0'));

		$config->type_range = ConfigType::MAP_PROVIDER->value;
		self::assertEquals('Error: Wrong property for key, expected a valid map provider, got something.', $config->sanity('something'));

		$config->type_range = '1|2|3|4';
		self::assertEquals('Error: Wrong property for key, expected 1 or 2 or 3 or 4, got 5.', $config->sanity('5'));
	}
}