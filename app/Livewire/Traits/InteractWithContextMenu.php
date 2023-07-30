<?php

namespace App\Livewire\Traits;

use App\Livewire\Components\Base\ContextMenu;

trait InteractWithContextMenu
{
	/**
	 * Open ContextMenu with form and paramters.
	 *
	 * @param string $form   Livewire component to include in the modal
	 * @param array  $params Parameters for said component
	 *
	 * @return void
	 */
	protected function openContextMenu(string $form, $params = []): void
	{
		$this->dispatch('openContextMenu', $form, $params)->to(ContextMenu::class);
	}

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
