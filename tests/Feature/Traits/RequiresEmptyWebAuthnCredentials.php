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

use Illuminate\Support\Facades\DB;

trait RequiresEmptyWebAuthnCredentials
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyWebAuthnCredentials(): void
	{
		// Assert that webauthn_credentials table is empty
		$this->assertDatabaseCount('webauthn_credentials', 0);
	}

	protected function tearDownRequiresEmptyWebAuthnCredentials(): void
	{
		// Clean up remaining stuff from tests
		DB::table('webauthn_credentials')->truncate();
	}
}
