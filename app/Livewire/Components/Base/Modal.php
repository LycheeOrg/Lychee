<?php

declare(strict_types=1);

namespace App\Livewire\Components\Base;

use App\Contracts\Livewire\Openable;
use App\Livewire\Traits\UseOpenable;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal component, extends Openable.
 *
 * This aims to encapsulate any floating box that appears in Lychee Interface:
 * - login
 * - privacy properties...
 */
class Modal extends Component implements Openable
{
	use UseOpenable;

	/**
	 * @var string defines the type of Modal.
	 *             This correspond to the Livewire component loaded inside the Modal.
	 */
	public string $type = '';

	/**
	 * Defines if we include a close button.
	 * if '' => no close button
	 * any other string correspond to the LANG text.
	 *
	 * @var string
	 */
	public string $close_text = '';

	/**
	 * @var array<string,string[]|string> defines the arguments to be passed to the
	 *                                    Livewire component loaded inside the Modal
	 */
	public array $params = [];

	/**
	 * Css properties for the modal.
	 *
	 * @var string
	 */
	public string $modalSize = 'md:max-w-xl';

	/**
	 * Open a Modal.
	 *
	 * @param string                             $type       defines the Component loaded inside the modal
	 * @param string                             $close_text text to put if we use a close button
	 * @param array<string,string[]|string|null> $params     Arguments to pass to the modal
	 *
	 * @return void
	 */
	#[On('openModal')]
	public function openModal(string $type, string $close_text = '', array $params = []): void
	{
		$this->open();
		$this->type = $type;
		$this->close_text = $close_text;
		$this->params = $params;
	}

	/**
	 * Close the Modal component.
	 *
	 * @return void
	 */
	#[On('closeModal')]
	public function closeModal(): void
	{
		$this->close();
	}

	/**
	 * Rendering of the Component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.components.modal');
	}
}
