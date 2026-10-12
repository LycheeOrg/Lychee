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

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * `CACHE_STORE` selects the cache store, `CACHE_DRIVER` is read when it is
 * unset (FR-090-15).
 */
class CacheStoreEnvTest extends AbstractTestCase
{
	private const VARIABLES = ['CACHE_STORE', 'CACHE_DRIVER'];

	/** @var array<string,array{server:mixed,env:mixed,putenv:string|false}> */
	private array $saved = [];

	protected function setUp(): void
	{
		parent::setUp();
		foreach (self::VARIABLES as $name) {
			$this->saved[$name] = ['server' => $_SERVER[$name] ?? null, 'env' => $_ENV[$name] ?? null, 'putenv' => getenv($name)];
		}
	}

	protected function tearDown(): void
	{
		foreach ($this->saved as $name => $value) {
			$this->setVariable($name, $value['putenv'] === false ? null : $value['putenv']);
			if ($value['server'] !== null) {
				$_SERVER[$name] = $value['server'];
			}
			if ($value['env'] !== null) {
				$_ENV[$name] = $value['env'];
			}
		}
		parent::tearDown();
	}

	/**
	 * @return array<string,array{string|null,string|null,string}>
	 */
	public static function environmentProvider(): array
	{
		return [
			'CACHE_STORE wins' => ['redis', 'file', 'redis'],
			'CACHE_DRIVER only' => [null, 'database', 'database'],
			'neither' => [null, null, 'file'],
		];
	}

	#[DataProvider('environmentProvider')]
	public function testCacheStoreThenCacheDriverThenFile(?string $cache_store, ?string $cache_driver, string $expected): void
	{
		$this->setVariable('CACHE_STORE', $cache_store);
		$this->setVariable('CACHE_DRIVER', $cache_driver);

		$cache = require base_path('config/cache.php');
		$data = require base_path('config/data.php');

		self::assertSame($expected, $cache['default']);
		self::assertSame($expected, $data['structure_caching']['cache']['store']);
	}

	private function setVariable(string $name, ?string $value): void
	{
		if ($value === null) {
			unset($_SERVER[$name], $_ENV[$name]);
			putenv($name);

			return;
		}

		$_SERVER[$name] = $value;
		$_ENV[$name] = $value;
		putenv($name . '=' . $value);
	}
}
