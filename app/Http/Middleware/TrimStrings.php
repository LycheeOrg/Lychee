<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
	/**
	 * The names of the attributes that should not be trimmed.
	 *
	 * @var array<int,string>
	 */
	protected $except = [
		'password',
		'password_confirmation',
		'needle', // Specific to renamer rules
		'replacement', // Specific to renamer rules
	];

	protected static $neverTrim = [
		'password',
		'password_confirmation',
		'needle', // Specific to renamer rules
		'replacement', // Specific to renamer rules
	];
}
