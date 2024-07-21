<?php

namespace App\Legacy\V1\RuleSets\User;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;

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
