<?php

namespace App\Rules;

use App\Facades\Helpers;
use Illuminate\Contracts\Validation\Rule;

class LicenseRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
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
