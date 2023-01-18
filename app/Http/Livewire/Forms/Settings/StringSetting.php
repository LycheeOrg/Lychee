<?php

namespace App\Http\Livewire\Forms\Settings;

use App\Facades\Lang;
use App\Models\Configs;
use Livewire\Component;

class StringSetting extends Component
{
	public Configs $config;
	public string $description;
	public string $placeholder;
	public string $action;
	public string $value; // ! Wired

	public function mount(string $description, string $name, string $placeholder, string $action)
	{
		$this->description = Lang::get($description);
		$this->action = Lang::get($action);
		$this->placeholder = Lang::get($placeholder);
		$this->config = Configs::where('key', '=', $name)->firstOrFail();
	}

	public function render()
	{
		$this->value = $this->config->value;

		return view('livewire.forms.form-input');
	}

	public function save()
	{
		$this->config->value = $this->value;
		$this->config->save();
	}
}