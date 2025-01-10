<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
