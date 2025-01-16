<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Settings;

use App\Contracts\Http\Requests\HasConfigs;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasConfigsTrait;
use App\Http\Resources\Editable\EditableConfigResource;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use App\Rules\ConfigKeyRequireSupportRule;
use App\Rules\ConfigKeyRule;
use App\Rules\ConfigValueRule;
use Illuminate\Support\Facades\Gate;

class SetConfigsRequest extends BaseApiRequest implements HasConfigs
{
	use HasConfigsTrait;
	/**
	 * Indicates if the validator should stop on the first rule failure.
	 * This is required because if the key is not valid then the value is irrelevant.
	 *
	 * @var bool
	 */
	protected $stopOnFirstFailure = true;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::CONFIGS_ATTRIBUTE => ['required'],
			RequestAttribute::CONFIGS_ARRAY_KEY_ATTRIBUTE => ['required', new ConfigKeyRule(), new ConfigKeyRequireSupportRule($this->verify)],
			RequestAttribute::CONFIGS_ARRAY_VALUE_ATTRIBUTE => ['present', new ConfigValueRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$editableConfigs = [];
		foreach ($values[RequestAttribute::CONFIGS_ATTRIBUTE] as $config) {
			$editableConfigs[] = new EditableConfigResource($config[RequestAttribute::CONFIGS_KEY_ATTRIBUTE], $config[RequestAttribute::CONFIGS_VALUE_ATTRIBUTE]);
		}
		$this->configs = collect($editableConfigs);
	}
}