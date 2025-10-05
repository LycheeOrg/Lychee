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

	public bool $is_photo;
	public bool $is_album;

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
			'is_photo' => ['required', 'boolean'],
			'is_album' => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->candidate = $values['candidate'];
		$this->is_photo = self::toBoolean($values['is_photo']);
		$this->is_album = self::toBoolean($values['is_album']);
	}
}
