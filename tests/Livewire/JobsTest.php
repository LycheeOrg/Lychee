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

namespace Tests\Livewire;

use App\Livewire\Components\Pages\Jobs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class JobsTest extends BaseLivewireTest
{
	public function testJobsLoggedOut(): void
	{
		Livewire::test(Jobs::class)
			->assertForbidden();
	}

	public function testJobsLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Jobs::class)
			->assertViewIs('livewire.pages.jobs');
	}
}
