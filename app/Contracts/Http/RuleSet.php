<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http;

/**
 * In order to avoid code duplication, we centralize the rule sets
 * used during the validation of requests as they are used both
 * in Livewire and in the Requests class.
 */
interface RuleSet
{
	/**
	 * Return an array containing the rules to be applied to the request attributes.
	 *
	 * @return array<string,string|array<int,string|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Enum>>
	 */
	public static function rules(): array;

	// TODO: Associate error message to above rules.
}
