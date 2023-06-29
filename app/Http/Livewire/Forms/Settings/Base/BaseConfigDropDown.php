<?php

namespace App\Http\Livewire\Forms\Settings\Base;

use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Basic drop down menu.
 * Do note that it will save and update the value immediately.
 * No confirmation is requested.
 */
abstract class BaseConfigDropDown extends Component
{
	public Configs $config;
	public string $description;
	public string $value; // ! Wired

	/**
	 * Renders the view of the dropdown menu.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->value = $this->config->value;

		return view('livewire.forms.form-drop-down');
	}

	/**
	 * This runs before a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function updating($field, $value): void
	{
		// TODO: VALIDATE & AUTHORIZE

		$this->config->value = $value;
		$this->config->save();
	}

	/**
	 * Defines accessor for the drop down options1.
	 *
	 * @return array
	 */
	abstract public function getOptionsProperty(): array;
}