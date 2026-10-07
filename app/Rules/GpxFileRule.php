<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Services\GpxValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Validates that an uploaded track is a genuine GPX file.
 *
 * @see GpxValidation
 */
final class GpxFileRule implements ValidationRule
{
	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (!$value instanceof UploadedFile) {
			$fail($attribute . ' must be a file.');

			return;
		}

		$error = resolve(GpxValidation::class)->validate($value);
		if ($error !== null) {
			$fail($attribute . ' ' . $error);
		}
	}
}
