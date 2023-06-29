<?php

namespace App\Http\Livewire\Forms\Settings\Base;

use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Livewire\Component;

/**
 * Basic boolean toggle.
 * No confirmation is requested.
 */
class BooleanSetting extends Component
{
	public Configs $config;
	public string $description;
	public string $footer;
	public bool $flag; // ! Wired

	/**
	 * Mount the toggle.
	 *
	 * @param string $description - LANG key
	 * @param string $name        - Name of the config attribute
	 * @param string $footer      - text under the toggle if necessary
	 *
	 * @return void
	 */
	public function mount(string $description, string $name, string $footer = ''): void
	{
		$this->description = __('lychee.' . $description);
		$this->footer = $footer !== '' ? __('lychee.' . $footer) : '';
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	/**
	 * Render the toggle element.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->flag = $this->config->value === '1';

		return view('livewire.forms.form-toggle');
	}

	/**
	 * This runs before a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws \RuntimeException
	 */
	public function updating($field, $value)
	{
		// TODO: VALIDATE & AUTHENTITCATE

		$this->config->value = $value === true ? '1' : '0';
		$this->config->save();
	}
}