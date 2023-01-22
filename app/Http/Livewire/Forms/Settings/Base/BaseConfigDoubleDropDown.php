<?php

namespace App\Http\Livewire\Forms\Settings\Base;

use App\Models\Configs;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Livewire\Component;

/**
 * @property array $options
 */
abstract class BaseConfigDoubleDropDown extends Component
{
	public string $begin;
	public string $middle;
	public string $end;

	public Configs $config1;
	public Configs $config2;
	public string $value1; // ! Wired
	public string $value2; // ! Wired

	public function render()
	{
		$this->value1 = $this->config1->value;
		$this->value2 = $this->config2->value;

		return view('livewire.forms.form-double-drop-down');
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
	public function updated($field, $value)
	{
		$this->config1->value = $this->value1;
		$this->config1->save();
		$this->config2->value = $this->value2;
		$this->config2->save();
	}

	abstract public function getOptions1Property(): array;

	abstract public function getOptions2Property(): array;
}