<?php

namespace App\Http\Livewire\Forms\Settings\Base;

use App\Models\Configs;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Livewire\Component;

/**
 * @property array $options
 */
abstract class BaseConfigDropDown extends Component
{
	public Configs $config;
	public string $description;
	public string $value; // ! Wired

	public function render()
	{
		$this->value = $this->config->value;

		return view('livewire.form.form-drop-down');
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
		$this->config->value = $value;
		$this->config->save();
	}

	abstract public function getOptionsProperty(): array;
}