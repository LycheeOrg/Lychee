<?php

namespace App\Rules;

/**
 * Conversion from Depregrated Rule to Validation Rule in Laravel 9 -> 10.
 */
trait ValidateTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail($this->message());
		}
	}
}
