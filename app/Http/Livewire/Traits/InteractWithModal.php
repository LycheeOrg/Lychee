<?php

namespace App\Http\Livewire\Traits;

trait InteractWithModal
{
	protected function openModal(string $form, $params = [])
	{
		$this->emitTo('components.modal', 'showModal', $form, $params);
	}

	protected function closeModal()
	{
		$this->emitTo('components.modal', 'closeModal');
		$this->closeDelete();
	}

	protected function closeDelete()
	{
		$this->emitTo('components.delete-modal', 'closeModal');
	}

	protected function deleteModal($model)
	{
		$params = [
			'id' => $model->id,
			'className' => get_class($model),
		];
		$this->emitTo('component.delete-modal', 'deleteModal', $params);
	}
}