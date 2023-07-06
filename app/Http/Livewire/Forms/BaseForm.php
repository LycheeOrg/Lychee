<?php

namespace App\Http\Livewire\Forms;

use App\Http\Livewire\Traits\InteractWithModal;
use Illuminate\Contracts\View\View;
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
	 * @var array attributes, this implies that rules
	 */
	public array $form = [];

	/**
	 * List of attributes names which are hidden.
	 *
	 * @var array
	 */
	public array $formHidden = [];

	/**
	 * @var array mapped between attributes and their Lang info
	 */
	public array $formLocale = [];

	/**
	 * @var string bypass form rendering with specific ones
	 */
	public string $render = '-modal';

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * This makes sure that rules are prefixed with `form.`.
	 *
	 * @return array
	 */
	final protected function rules(): array
	{
		return collect($this->getRuleSet())->mapWithKeys(fn ($v, $k) => ['form.' . $k => $v])->all();
	}

	/**
	 * This defines the attributes names that we are validating.
	 * They are displayed in the error messages.
	 *
	 * @return array
	 */
	final protected function getValidationAttributes(): array
	{
		return collect($this->getRuleSet())->mapWithKeys(fn ($v, $k) => ['form.' . $k => $k])->all();
	}

	/**
	 * Return the rules to be applied on the form.
	 *
	 * @return array
	 */
	abstract protected function getRuleSet(): array;

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
		/** @var view-string $view */
		$view = 'livewire.forms.form' . $this->render;

		return view($view);
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
