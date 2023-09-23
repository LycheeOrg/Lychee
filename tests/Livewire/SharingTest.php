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

use App\Livewire\Components\Pages\Sharing;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SharingTest extends BaseLivewireTest
{
	public function testSharingLoggedOut(): void
	{
		Livewire::test(Sharing::class)
			->assertForbidden();
	}

	public function testSharingLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Sharing::class)
			->assertViewIs('livewire.pages.sharing');
	}
}
