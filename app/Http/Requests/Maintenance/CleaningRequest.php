<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Maintenance;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CleaningRequest extends BaseApiRequest
{
	private string $path;

	public function rules(): array
	{
		return [
			RequestAttribute::SINGLE_PATH_ATTRIBUTE => [
				'required',
				'string',
				Rule::in([
					'filesystems.disks.extract-jobs.root',
					'filesystems.disks.image-jobs.root',
					'filesystems.disks.image-upload.root']),
			],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->path = config($values[RequestAttribute::SINGLE_PATH_ATTRIBUTE]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function path(): string
	{
		return $this->path;
	}
}
