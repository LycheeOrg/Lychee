<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Statistics;

use App\Contracts\Http\Requests\HasOwnerId;
use App\Enum\CountType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasOwnerIdTrait;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CountsRequest extends BaseApiRequest implements HasOwnerId
{
	use HasOwnerIdTrait;

	public int $min;
	public int $max;
	public CountType $type;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_SEE_STATISTICS, [Configs::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'max' => ['sometimes', 'integer', 'min:0'],
			'min' => ['sometimes', 'integer', 'min:0', 'gte:max'],
			'type' => ['required', 'string', Rule::enum(CountType::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->min = intval($values['min'] ?? 365);
		$this->max = intval($values['max'] ?? 0);
		$this->type = CountType::from($values['type']);

		// Filter only to user if user is not admin
		if (Auth::check() && Auth::user()?->may_administrate !== true) {
			$this->owner_id = intval(Auth::id());
		}
	}
}
