<?php

namespace App\Livewire\Forms;

use App\Models\Configs;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Form;

class AllConfigsForms extends Form
{
	/** @var Collection<Configs> */
	#[Locked]
	public Collection $configs;

	/** @var array<int,string> */
	public array $values;

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @var array<string,string>
	 */
	protected $rules = [
		'values.*' => 'nullable',
	];

	/**
	 * Initialize form data.
	 *
	 * @param Collection<Configs> $configs
	 *
	 * @return void
	 */
	public function setConfigs(Collection $configs): void
	{
		$this->configs = $configs;
		$this->values = $configs->map(fn (Configs $c, int $k) => $c->value)->all();
	}

	/**
	 * Save form data.
	 *
	 * @return void
	 *
	 * @throws ValidationException
	 */
	public function save(): void
	{
		$this->validate();
		$this->configs->each(
			function (Configs $c, int $key) {
				$candidateValue = $this->values[$key];
				$template = 'Error: Expected %s, got ' . ($candidateValue ?? 'NULL') . '.';

				$error_msg = $c->sanity($candidateValue, $template);
				if ($error_msg === '') {
					$c->value = $candidateValue;
					$c->save();
				} else {
					$this->addError('values.' . $key, $error_msg);
				}
			}
		);
	}
}