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

namespace Tests\Livewire\Base;

use App\Models\User;
use Tests\AbstractTestCase;

abstract class BaseLivewireTest extends AbstractTestCase
{
	protected User $admin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->admin = User::findOrFail(1);
		$this->withoutVite();
	}
}