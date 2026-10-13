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

namespace Tests\Unit;

use App\Models\ConfigCategory;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Ensure that every config and config category stored in the database
 * has its matching entries in lang/en/all_settings.php.
 *
 * Other locales are kept in sync with English by {@see LangTest}.
 */
class ConfigTranslationKeysTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private array $dictionary;

	public function setUp(): void
	{
		parent::setUp();
		$this->dictionary = include base_path('lang/en/all_settings.php');
	}

	public function testEveryConfigHasDocumentationKey(): void
	{
		$this->assertAllKeysPresent(Configs::query()->pluck('key')->all(), 'documentation');
	}

	public function testEveryConfigHasDetailsKey(): void
	{
		$this->assertAllKeysPresent(Configs::query()->pluck('key')->all(), 'details');
	}

	public function testEveryCategoryHasNameKey(): void
	{
		$this->assertAllKeysPresent(ConfigCategory::query()->pluck('cat')->all(), 'category_name');
	}

	public function testEveryCategoryHasDescriptionKey(): void
	{
		$this->assertAllKeysPresent(ConfigCategory::query()->pluck('cat')->all(), 'category_description');
	}

	/**
	 * @param string[] $keys    keys that must exist in the section
	 * @param string   $section section of all_settings.php to check
	 */
	private function assertAllKeysPresent(array $keys, string $section): void
	{
		static::assertNotEmpty($keys, 'No entries found in the database.');
		static::assertArrayHasKey($section, $this->dictionary);

		$missing = array_values(array_diff($keys, array_keys($this->dictionary[$section])));
		static::assertSame([], $missing, sprintf('lang/en/all_settings.php "%s" is missing the following keys: %s', $section, implode(', ', $missing)));
	}
}
