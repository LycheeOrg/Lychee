<?php

namespace App\Livewire\Components\Forms\Settings\Base;

use Illuminate\Contracts\View\View;

/**
 * String setting input.
 * To persist the data a call to save() is required.
 */
final class PasswordSetting extends StringSetting
{
	/**
	 * Renders the input form.
	 *
	 * @return View
	 */
	final public function render(): View
	{
		$this->value = $this->config->value;

		return view('livewire.forms.settings.password');
	}
}