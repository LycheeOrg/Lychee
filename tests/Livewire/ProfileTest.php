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

use App\Livewire\Components\Pages\Profile;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ProfileTest extends BaseLivewireTest
{
	public function testProfileLoggedOut(): void
	{
		Livewire::test(Profile::class)
			->assertForbidden();
	}

	public function testProfileLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Profile::class)
			->assertViewIs('livewire.pages.profile');
	}
}
