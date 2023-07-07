<?php

namespace App\Http\Livewire\Traits;

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
		$this->emitTo('components.base.context-menu', 'openContextMenu', $form, $params);
	}

	/**
	 * Close the ContextMenu.
	 *
	 * @return void
	 */
	protected function closeContextMenu(): void
	{
		$this->emitTo('components.base.context-menu', 'closeContextMenu');
	}
}
