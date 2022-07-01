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

	/**
	 * This defined the events that the Component will intercept.
	 * In order to facilitate the use of those events, the trait
	 * app/Livewire/Traits/InteractWithModal.php can be used to
	 * add access to the modal.
	 *
	 * @var string[] listeners for modal events
	 * */
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
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modal');
	}
}
