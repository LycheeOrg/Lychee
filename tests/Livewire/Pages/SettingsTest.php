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

use App\Livewire\Components\Pages\Settings;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Traits\RequiresEmptyUsers;

class SettingsTest extends BaseLivewireTest
{
	use RequiresEmptyUsers;

	private string $component = Settings::class;

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertForbidden();
	}

	public function testLoggedInAdmin(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.pages.settings');

		Livewire::actingAs($this->admin)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}
}
