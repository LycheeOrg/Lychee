<?php

namespace App\Http\RuleSets\WebAuthn;

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
