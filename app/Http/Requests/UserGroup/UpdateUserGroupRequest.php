<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\UserGroup;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasName;
use App\Contracts\Http\Requests\HasUserGroup;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasNameTrait;
use App\Http\Requests\Traits\HasUserGroupTrait;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use App\Rules\StringRequireSupportRule;
use Illuminate\Support\Facades\Gate;

class UpdateUserGroupRequest extends BaseApiRequest implements HasUserGroup, HasDescription, HasName
{
	use HasUserGroupTrait;
	use HasDescriptionTrait;
	use HasNameTrait;

	public function authorize(): bool
	{
		return Gate::check(UserGroupPolicy::CAN_EDIT, [UserGroup::class, $this->user_group]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::GROUP_ID => ['required', 'int'],
			RequestAttribute::NAME_ATTRIBUTE => ['required', 'string', 'max:255'],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['nullable', 'string', new StringRequireSupportRule('', $this->verify)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = $values[RequestAttribute::NAME_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE] ?? null;
		$this->user_group = UserGroup::with('users')->findOrFail($values[RequestAttribute::GROUP_ID]);
	}
}
