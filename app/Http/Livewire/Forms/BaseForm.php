<?php

namespace App\Http\Livewire\Forms;

use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Basic form component
 * Livewire forms need to extends from this.
 */
abstract class BaseForm extends Component
{
	/*
	 * Allow modal integration
	 */
	use InteractWithModal;

	public string $title = '';
	public string $validate = '';
	public string $cancel = '';
	public array $params = [];

	/**
	 * @var array attributes
	 */
	public array $form = [];

	/**
	 * @var array mapped between attributes and their Lang info
	 */
	public array $formLocale = [];

	/**
	 * @var string bypass form rendering with specific ones
	 */
	public string $render = '';

	/**
	 * A form has a Submit method.
	 *
	 * @return void
	 */
	abstract public function submit(): void;

	/**
	 * We load the parameters.
	 *
	 * @param array $params set of parameters of the form
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		$this->params = $params;
	}

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.form.form' . $this->render);
	}

	/**
	 * Add an handle to close the modal form from a user-land call.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
