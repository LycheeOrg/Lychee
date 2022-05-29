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

namespace Tests\Feature\Traits;

use App\Models\Configs;
use Tests\TestCase;

trait RequiresImageHandler
{
	protected int $hasImagickInit;

	protected function setUpRequiresImagick(): void
	{
		$this->hasImagickInit = (int) Configs::get_value(TestCase::CONFIG_HAS_IMAGICK, 1);
		Configs::set(TestCase::CONFIG_HAS_IMAGICK, 1);

		if (!Configs::hasImagick()) {
			static::markTestSkipped('Imagick is not available. Test Skipped.');
		}
	}

	protected function setUpRequiresGD(): void
	{
		$this->hasImagickInit = (int) Configs::get_value(TestCase::CONFIG_HAS_IMAGICK, 1);
		Configs::set(TestCase::CONFIG_HAS_IMAGICK, 0);

		if (Configs::hasImagick()) {
			static::markTestSkipped('Imagick still enabled although it shouldn\'t. Test Skipped.');
		}
	}

	protected function tearDownRequiresImageHandler(): void
	{
		Configs::set(TestCase::CONFIG_HAS_IMAGICK, $this->hasImagickInit);
	}

	abstract public static function markTestSkipped(string $message = ''): void;
}
