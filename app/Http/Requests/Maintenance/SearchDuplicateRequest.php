<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use App\Rules\BooleanRequireSupportRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Request
 */
class SearchDuplicateRequest extends BaseApiRequest
{
	public bool $with_album_constraint;
	public bool $with_checksum_constraint;
	public bool $with_title_constraint;

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
			'with_album_constraint' => ['required', new BooleanRequireSupportRule(false, $this->verify)], // : false,
			'with_checksum_constraint' => ['required', new BooleanRequireSupportRule(true, $this->verify)], // : true,
			'with_title_constraint' => ['required', new BooleanRequireSupportRule(false, $this->verify)], // : false
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->with_album_constraint = static::toBoolean($values['with_album_constraint']);
		$this->with_checksum_constraint = static::toBoolean($values['with_checksum_constraint']);
		$this->with_title_constraint = static::toBoolean($values['with_title_constraint']);
	}
}
