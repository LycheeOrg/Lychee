<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\RenamerRule;
use Illuminate\Support\Facades\Auth;

/**
 * Request for deleting a renamer rule.
 */
class DeleteRenamerRuleRequest extends BaseApiRequest
{
	public RenamerRule $renamer_rule;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();

		if ($user === null || (!$user->may_administrate && !$user->may_upload)) {
			return false;
		}

		// Users can only delete their own rules
		return $this->renamer_rule->owner_id === $user->id || $user->may_administrate;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::RENAMER_RULE_ID_ATTRIBUTE => ['required', 'integer'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->renamer_rule = RenamerRule::findOrFail($values[RequestAttribute::RENAMER_RULE_ID_ATTRIBUTE]);
	}
}
