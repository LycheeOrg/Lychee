<?php

namespace App\Rules;

use App\Models\Configs;
use Illuminate\Contracts\Validation\ValidationRule;

class ConfigKeyRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_string($value) === false) {
			$fail($attribute . ' is not a string');

			return;
		}

		/** @var string $value */
		if (!array_key_exists($value, Configs::get())) {
			$fail($attribute . ' is not a valid configuration key.');

			return;
		}
	}
}
