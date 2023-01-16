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

use Illuminate\Support\Facades\Auth;

trait RequiresAdmin
{
	protected function assertIsAdminOrSkip(): void
	{
		if (Auth::user()?->may_administrate !== true) {
			static::markTestSkipped("Test only relevant if executed as admin user.");
		}
	}

	abstract public static function markTestSkipped(string $message = ''): void;
}