<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Models\Configs;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Livewire\Component;

class BooleanSetting extends Component
{
	public Configs $config;
	public string $description;
	public string $footer;
	public bool $flag; // ! Wired

	public function mount(string $description, string $name, string $footer = '')
	{
		$this->description = Lang::get($description);
		$this->footer = $footer !== '' ? Lang::get($footer) : '';
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	public function render()
	{
		$this->flag = $this->config->value === '1';

		return view('livewire.form.form-toggle');
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
		$this->config->value = $value === true ? '1' : '0';
		$this->config->save();
	}
}