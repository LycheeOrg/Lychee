<?php

declare(strict_types=1);

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

use function Safe\fileowner;

trait InteractsWithFilesystemPermissions
{
	protected static int $effUserId;

	protected function setUpInteractsWithFilesystemPermissions(): void
	{
		self::$effUserId = posix_geteuid();
	}

	protected static function skipIfNotFileOwner(string $path): void
	{
		if (fileowner($path) !== static::$effUserId) {
			self::markTestSkipped(sprintf('User running the test must be owner of %s.', $path));
		}
	}
}
