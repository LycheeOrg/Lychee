<?php

namespace App\Livewire\Forms;

use App\Exceptions\Internal\LycheeAssertionError;
use App\Models\Configs;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Form;

class AllConfigsForms extends Form
{
	/** @var array<int,Configs> */
	#[Locked]
	public array $configs;

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
		// ! IMPORTANT, WE MUST use all() to get an array and not use the collections,
		// ! otherwise this messes up the ordering.
		$this->configs = $configs->all();
		$this->values = $configs->map(fn (Configs $c, int $k) => $c->value)->all();
	}

	/**
	 * Save form data.
	 *
	 * @return void
	 *
	 * @throws ValidationException
	 * @throws LycheeAssertionError
	 */
	public function save(): void
	{
		$this->validate();
		$n = count($this->values);
		if ($n !== count($this->configs)) {
			throw new LycheeAssertionError('Number of values do not match number of configs');
		}

		for ($idx = 0; $idx < $n; $idx++) {
			$c = $this->configs[$idx];
			$candidateValue = $this->values[$idx];
			$template = 'Error: Expected %s, got ' . ($candidateValue ?? 'NULL') . '.';

			$error_msg = $c->sanity($candidateValue, $template);
			if ($error_msg === '') {
				$c->value = $candidateValue;
				$c->save();
			} else {
				$this->addError('values.' . $idx, $error_msg);
			}
		}
	}
}
