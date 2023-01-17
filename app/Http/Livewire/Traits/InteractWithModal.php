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
		$this->emitTo('components.base.modal', 'openModal', $form, '', $params);
	}

	/**
	 * Open Modal with form and paramters.
	 *
	 * @param string $form       Livewire component to include in the modal
	 * @param string $close_text text to put if we use a close button
	 * @param array  $params     Parameters for said component
	 *
	 * @return void
	 */
	protected function openClosableModal(string $form, string $close_text, $params = []): void
	{
		$this->emitTo('components.base.modal', 'openModal', $form, $close_text, $params);
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
