<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Http\Requests\BaseApiRequest;
use App\Rules\StringRule;
use Illuminate\Support\Facades\Auth;

/**
 * Request for testing renamer rules against a candidate string.
 */
class TestRenamerRequest extends BaseApiRequest
{
	public string $candidate;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();

		return $user !== null && ($user->may_administrate || $user->may_upload);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'candidate' => ['required', new StringRule(false, 1000)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->candidate = $values['candidate'];
	}
}
