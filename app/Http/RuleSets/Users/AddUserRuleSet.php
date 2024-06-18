<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Users;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

/**
 * Rules applied when creating a new user.
 */
class AddUserRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
			RequestAttribute::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE => 'present|boolean',
		];
	}
}
