<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\RuleSets\WebAuthn;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;

/**
 * Rules applied when deleting a credential.
 */
class DeleteCredentialRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [RequestAttribute::ID_ATTRIBUTE => 'required|string'];
	}
}
