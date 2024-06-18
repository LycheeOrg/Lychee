<?php

declare(strict_types=1);

namespace App\Http\RuleSets\WebAuthn;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;

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
