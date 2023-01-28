<?php

namespace App\Http\RuleSets\User;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;

/**
 * Rules applied when setting a email for notifications.
 */
class SetEmailRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [
			RequestAttribute::EMAIL_ATTRIBUTE => 'present|nullable|email:rfc,dns|max:100',
		];
	}
}
