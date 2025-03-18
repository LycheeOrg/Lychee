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

namespace Tests\Unit;

use function Safe\scandir;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Tests\AbstractTestCase;

class LangTest extends AbstractTestCase
{
	private ConsoleSectionOutput $msgSection;
	private bool $failed = false;

	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testLanguageConsistency(): void
	{
		/** @disregard P1013 */
		static::assertEquals('en', app()->getLocale());
		static::assertEquals('Gallery', __('gallery.title'));

		$this->msgSection = (new ConsoleOutput())->section();

		/** @var array<int,string> $englishDictionaries */
		$english_dictionaries = collect(array_diff(scandir(base_path('lang/en')), ['..', '.']))->filter(fn ($v) => str_ends_with($v, '.php'))->all();
		foreach ($english_dictionaries as $dictionary_file) {
			$english_dictionary = include base_path('lang/en/' . $dictionary_file);
			$available_dictionaries = collect(array_diff(config('app.supported_locale'), ['en']))->filter(fn ($v) => is_dir(base_path('lang/' . $v)))->all();

			foreach ($available_dictionaries as $locale) {
				$dictionary = include base_path('lang/' . $locale . '/' . $dictionary_file);
				$this->recursiveCheck($english_dictionary, $dictionary, $locale, $dictionary_file);
			}
		}
		static::assertFalse($this->failed);
	}

	public function testEnglishAsFallbackIfLangConfigIsMissing(): void
	{
		/** @disregard P1013 */
		app()->setLocale('ZK');
		/** @disregard P1013 */
		static::assertEquals('ZK', app()->getLocale());
		static::assertEquals('Gallery', __('gallery.title'));
	}

	private function recursiveCheck(array $expected, array $candidate, string $locale, string $file, string $prefix = ''): void
	{
		$missing_keys = array_diff_key($expected, $candidate);

		foreach ($missing_keys as $key => $value) {
			$this->msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s %s misses the following key: %s', str_pad($locale, 8), $file, $prefix . $key));
			$this->failed = true;
		}

		$extra_keys = array_diff_key($candidate, $expected);
		foreach ($extra_keys as $key => $value) {
			$this->msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s %s has the following extra key: %s', str_pad($locale, 8), $file, $prefix . $key));
			$this->failed = true;
		}

		$expected_arrays = array_filter($expected, fn ($v) => is_array($v));
		$candidate_arrays = array_filter($candidate, fn ($v) => is_array($v));
		foreach ($expected_arrays as $key => $sub_expected) {
			$sub_candidate = $candidate_arrays[$key] ?? [];
			$this->recursiveCheck($sub_expected, $sub_candidate, $locale, $file, $key . '.');
		}
	}
}
