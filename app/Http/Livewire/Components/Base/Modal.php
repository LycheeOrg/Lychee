<?php

namespace App\Http\Livewire\Components\Base;

use Illuminate\View\View;

/**
 * Modal component, extends Openable.
 *
 * This aims to encapsulate any floating box that appears in Lychee Interface:
 * - login
 * - privacy properties...
 */
class Modal extends Openable
{
	/** @var string defines the opacity status (unused for now) */
	public string $opacity = '0';

	/**
	 * ! defines the type of Modal. This correspond to the Livewire component loaded inside the Modal.
	 *
	 * @var string
	 */
	public string $type = '';

	/**
	 * ! defines the arguments to be passed to the Livewire component loaded inside the Modal.
	 *
	 * @var array
	 */
	public array $params = [];

	/**
	 * Css properties for the modal.
	 *
	 * @var string
	 */
	public string $modalSize = 'md:max-w-xl';

	/** @var string[] Listeners for modal events. */
	protected $listeners = [
		'openModal',
		'closeModal',
		'deleteModal',
	];

	/**
	 * Open a Modal.
	 *
	 * @param string $type   defines the Component loaded inside the modal
	 * @param array  $params Arguments to pass to the modal
	 *
	 * @return void
	 */
	public function openModal(string $type, array $params = []): void
	{
		$this->open();
		$this->type = $type;
		$this->params = $params;
		$this->opacity = '100';
	}

	/**
	 * ? unused for now.
	 */
	public function deleteModal($params, string $form = 'forms.base-delete-form'): void
	{
		$this->openModal($form, $params);
	}

	/**
	 * Close the Modal component.
	 *
	 * @return void
	 */
	public function closeModal(): void
	{
		$this->close();
		$this->opacity = '0';
	}

	/**
	 * Rendering of the Component.
	 *
	 * @return void
	 */
	public function render(): View
	{
		return view('livewire.modal');
	}
}
