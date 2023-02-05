<?php

namespace App\Http\RuleSets\Session;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

/**
 * Rules applied when trying to log in.
 */
class LoginRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}
}
