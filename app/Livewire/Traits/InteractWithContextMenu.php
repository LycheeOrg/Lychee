<?php

namespace App\Livewire\Traits;

use App\Livewire\Components\Base\ContextMenu;

trait InteractWithContextMenu
{
	/**
	 * Close the ContextMenu.
	 *
	 * @return void
	 */
	protected function closeContextMenu(): void
	{
		$this->dispatch('closeContextMenu')->to(ContextMenu::class);
	}
}
