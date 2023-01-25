<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use Tests\AbstractTestCase;

class LangTest extends AbstractTestCase
{
	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testLanguageConsistency(): void
	{
		static::assertEquals('en', app()->getLocale());
		static::assertEquals('OK', __('lychee.SUCCESS'));
	}

	public function testEnglishAsFallbackIfLangConfigIsMissing(): void
	{
		app()->setLocale('ZK');
		static::assertEquals('ZK', app()->getLocale());
		static::assertEquals('OK', __('lychee.SUCCESS'));
	}
}
