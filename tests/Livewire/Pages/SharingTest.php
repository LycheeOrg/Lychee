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

use App\Livewire\Components\Pages\Sharing;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SharingTest extends BaseLivewireTest
{
	private string $component = Sharing::class;

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedInUserNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test($this->component)
			->assertForbidden();
	}

	public function testLoggedInUser(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->assertViewIs('livewire.pages.sharing');

		Livewire::actingAs($this->userMayUpload1)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}

	public function testLoggedInAdmin(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.pages.sharing');

		Livewire::actingAs($this->admin)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}
}
