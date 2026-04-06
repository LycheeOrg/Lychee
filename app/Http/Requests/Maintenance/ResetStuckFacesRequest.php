<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Request for the resetStuckFaces maintenance endpoints.
 * Admin-only. Optionally accepts an older_than_minutes parameter.
 */
class ResetStuckFacesRequest extends BaseApiRequest
{
	private int $older_than_minutes = 60;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'older_than_minutes' => 'sometimes|integer|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->older_than_minutes = isset($values['older_than_minutes']) ? (int) $values['older_than_minutes'] : 60;
	}

	public function olderThanMinutes(): int
	{
		return $this->older_than_minutes;
	}
}
