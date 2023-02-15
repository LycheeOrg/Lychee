<?php

namespace App\Rules;

use App\Facades\Helpers;
use Illuminate\Contracts\Validation\ValidationRule;

class LicenseRule implements ValidationRule
{
	use ValidateTrait;

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		return
			is_string($value) &&
			array_search($value, Helpers::get_all_licenses(), true) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be one out of: ' .
			implode(', ', Helpers::get_all_licenses());
	}
}
