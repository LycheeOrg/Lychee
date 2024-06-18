<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Components\Base\Modal;

trait InteractWithModal
{
	/**
	 * Open Modal with form and paramters.
	 *
	 * @param string                             $form   Livewire component to include in the modal
	 * @param array<string,string[]|string|null> $params Parameters for said component
	 *
	 * @return void
	 */
	protected function openModal(string $form, array $params = []): void
	{
		$this->dispatch('openModal', $form, '', $params)->to(Modal::class);
	}

	/**
	 * Open Modal with form and paramters.
	 *
	 * @param string                             $form       Livewire component to include in the modal
	 * @param string                             $close_text text to put if we use a close button
	 * @param array<string,string|string[]|null> $params     Parameters for said component
	 *
	 * @return void
	 */
	protected function openClosableModal(string $form, string $close_text, array $params = []): void
	{
		$this->dispatch('openModal', $form, $close_text, $params)->to(Modal::class);
	}

	/**
	 * Close the modal.
	 *
	 * @return void
	 */
	protected function closeModal(): void
	{
		$this->dispatch('closeModal')->to(Modal::class);
	}
}
