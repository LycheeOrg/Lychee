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

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ConfigIntegrity;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Tests\AbstractTestCase;

class ConfigIntegrityTest extends AbstractTestCase
{
	private ConsoleSectionOutput $msgSection;
	private bool $failed = false;

	public function testConfiguration(): void
	{
		$this->msgSection = (new ConsoleOutput())->section();

		$keys = DB::table('configs')->select('key')->where('level', '=', '1')->pluck('key')->all();
		foreach ($keys as $key) {
			if (!in_array($key, ConfigIntegrity::SE_FIELDS, true)) {
				$this->msgSection->writeln(sprintf('<comment>Error:</comment> Key %s is not in the list of keys.', $key));
				$this->failed = true;
			}
		}

		static::assertFalse($this->failed);
	}

	public function testKeys(): void
	{
		/** @var string[] $keys */
		$keys = DB::table('configs')->select('key')->where('level', '=', '1')->pluck('key')->all();
		foreach (ConfigIntegrity::SE_FIELDS as $key) {
			if (!in_array($key, $keys, true)) {
				$this->msgSection->writeln(sprintf('<comment>Error:</comment> Key %s is not in the database.', $key));
				$this->failed = true;
			}
		}

		static::assertFalse($this->failed);
	}
}
