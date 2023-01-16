<?php

namespace App\Contracts\Http;

/**
 * In order to avoid code duplication, we centralize the rule sets
 * used during the validation of requests as they are used both
 * in Livewire and in the Requests class.
 */
interface RuleSet
{
	/**
	 * Return an array containing the rules to be applied to the request attributes
	 *
	 * @return array
	 */
	public static function rules(): array;

	// TODO: Associate error message to above rules.
}
