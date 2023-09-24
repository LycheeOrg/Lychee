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

namespace Tests\Livewire\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Base\ContextMenu;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ContextMenuTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(ContextMenu::class)
			->assertSet('isOpen', false)
			->dispatch('openContextMenu', 'menus.AlbumAdd', [Params::PARENT_ID => null], 'right: 30px; top: 30px; transform-origin: top right;')
			->assertSet('isOpen', true)
			->assertSet('type', 'menus.AlbumAdd')
			->dispatch('closeContextMenu')
			->assertSet('isOpen', false);
	}

	public function testMenu2(): void
	{
		Livewire::test(ContextMenu::class)
			->assertSet('isOpen', false)
			->call('openContextMenu', 'menus.AlbumAdd', [Params::PARENT_ID => null], 'right: 30px; top: 30px; transform-origin: top right;')
			->assertSet('isOpen', true)
			->assertSet('type', 'menus.AlbumAdd')
			->dispatch('closeContextMenu')
			->assertSet('isOpen', false);
	}
}
