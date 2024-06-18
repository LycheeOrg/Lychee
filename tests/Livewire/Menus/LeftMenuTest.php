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

namespace Tests\Livewire\Menus;

use App\Livewire\Components\Menus\LeftMenu;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class LeftMenuTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::actingAs($this->admin)->test(LeftMenu::class)
			->assertViewIs('livewire.components.left-menu')
			->assertSet('isOpen', false)
			->dispatch('openLeftMenu')
			->assertSet('isOpen', true)
			->dispatch('closeLeftMenu')
			->assertSet('isOpen', false)
			->dispatch('toggleLeftMenu')
			->assertSet('isOpen', true)
			->call('openAboutModal')
			->assertDispatched('openModal', 'modals.about')
			->call('logout')
			->assertRedirect(route('livewire-gallery'));
	}
}
