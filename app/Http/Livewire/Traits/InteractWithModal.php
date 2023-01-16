<?php

namespace App\Http\Livewire\Traits;

trait InteractWithModal
{
	/**
	 * Open Modal with form and paramters.
	 *
	 * @param string $form   Livewire component to include in the modal
	 * @param array  $params Parameters for said component
	 *
	 * @return void
	 */
	protected function openModal(string $form, $params = []): void
	{
		$this->emitTo('components.base.modal', 'openModal', $form, $params);
	}

	/**
	 * Close the modal.
	 *
	 * @return void
	 */
	protected function closeModal(): void
	{
		$this->emitTo('components.base.modal', 'closeModal');
	}
}
