<?php

namespace App\Http\Livewire\Forms\Settings\Base;

use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * String setting input.
 * To persist the data a call to save() is required.
 */
class StringSetting extends Component
{
	public Configs $config;
	public string $description;
	public string $placeholder;
	public string $action;
	public string $value; // ! Wired

	/**
	 * @param string $description - LANG key
	 * @param string $name        - name of the config attribute
	 * @param string $placeholder - LANG key
	 * @param string $action      - LANG key
	 *
	 * @return void
	 */
	public function mount(string $description, string $name, string $placeholder, string $action): void
	{
		$this->description = __('lychee.' . $description);
		$this->action = __('lychee.' . $action);
		$this->placeholder = __('lychee.' . $placeholder);
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	/**
	 * Renders the input form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->value = $this->config->value;

		return view('livewire.forms.form-input');
	}

	/**
	 * Validation call to persist the data (as opposed to drop down menu and toggle which are instant).
	 *
	 * @return void
	 */
	public function save(): void
	{
		$this->config->value = $this->value;
		$this->config->save();
	}
}