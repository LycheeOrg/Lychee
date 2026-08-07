<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * This rule is designed specifically to avoid path injection.
 */
final class FilenameRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_string($value) === false) {
			$fail(':attribute is not a string.');

			return;
		}

		if ($value === '.') {
			$fail(':attribute is not a valid file name.');

			return;
		}

		$dir_name = pathinfo($value, PATHINFO_DIRNAME);

		// Allow test files to be in subdirectories of tests/Samples,
		// but not in any other subdirectory.
		if (App::runningUnitTests() &&
			str_starts_with($dir_name, 'tests/Samples') &&
			!str_contains($dir_name, '..')
		) {
			return;
		}

		if ($dir_name !== '' && $dir_name !== '.') {
			$fail(':attribute contains a directory, give proper filename.');

			return;
		}

		if (Str::of($value)->contains(['..', '/', '\\'])) {
			$fail(':attribute contains invalid characters:.');

			return;
		}
	}
}
