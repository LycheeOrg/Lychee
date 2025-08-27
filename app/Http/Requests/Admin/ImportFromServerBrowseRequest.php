<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class ImportFromServerBrowseRequest extends BaseApiRequest
{
	public string $directory;

	public function authorize(): bool
	{
		// Only the owner of Lychee can use this functionality
		return Auth::user() !== null && Auth::user()->id === Configs::getValueAsInt('owner_id');
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'directory' => ['present', 'string'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->directory = $values['directory'] ?? '';
	}
}
