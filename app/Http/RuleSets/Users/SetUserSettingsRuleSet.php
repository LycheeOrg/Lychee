<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Users;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\IntegerIDRule;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

/**
 * Rules applied when updating a user.
 */
class SetUserSettingsRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [
			RequestAttribute::ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule(), 'min:1'],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(false)],
			RequestAttribute::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE => 'present|boolean',
		];
	}
}
