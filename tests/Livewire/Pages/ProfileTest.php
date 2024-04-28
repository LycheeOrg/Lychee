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

namespace Tests\Livewire\Pages;

use App\Livewire\Components\Pages\Profile;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ProfileTest extends BaseLivewireTest
{
	private string $component = Profile::class;

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertViewIs('livewire.pages.profile');

		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}

	public function testLoggedInPowerless(): void
	{
		Livewire::actingAs($this->userLocked)->test($this->component)
			->assertForbidden();
	}
}
