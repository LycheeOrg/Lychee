<?php

namespace App\Http\Livewire\Traits;

trait InteractWithModal
{
	protected function openModal(string $form, $params = []): void
	{
		$this->emitTo('components.modal', 'openModal', $form, $params);
	}

	protected function closeModal(): void
	{
		$this->emitTo('components.modal', 'closeModal');
		$this->closeDelete();
	}

	protected function closeDelete(): void
	{
		$this->emitTo('components.delete-modal', 'closeModal');
	}

	protected function deleteModal($model): void
	{
		$params = [
			'id' => $model->id,
			'className' => get_class($model),
		];
		$this->emitTo('component.delete-modal', 'deleteModal', $params);
	}
}
