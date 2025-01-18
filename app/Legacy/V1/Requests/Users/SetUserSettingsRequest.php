<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Users;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPassword;
use App\Legacy\V1\Contracts\Http\Requests\HasUser;
use App\Legacy\V1\Contracts\Http\Requests\HasUsername;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPasswordTrait;
use App\Legacy\V1\Requests\Traits\HasUsernameTrait;
use App\Legacy\V1\Requests\Traits\HasUserTrait;
use App\Legacy\V1\RuleSets\Users\SetUserSettingsRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

final class SetUserSettingsRequest extends BaseApiRequest implements HasUsername, HasPassword, HasUser
{
	use HasUsernameTrait;
	use HasPasswordTrait;
	use HasUserTrait;

	protected bool $mayUpload = false;
	protected bool $mayEditOwnSettings = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetUserSettingsRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		if (array_key_exists(RequestAttribute::PASSWORD_ATTRIBUTE, $values)) {
			// See {@link HasPasswordTrait::password()} for an explanation
			// of the semantic difference between `null` and `''`.
			$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE] ?? '';
		} else {
			// @codeCoverageIgnoreStart
			$this->password = null;
			// @codeCoverageIgnoreEnd
		}
		$this->mayUpload = static::toBoolean($values[RequestAttribute::MAY_UPLOAD_ATTRIBUTE]);
		$this->mayEditOwnSettings = static::toBoolean($values[RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE]);
		/** @var int $userID */
		$userID = $values[RequestAttribute::ID_ATTRIBUTE];
		$this->user2 = User::query()->findOrFail($userID);
	}

	public function mayUpload(): bool
	{
		return $this->mayUpload;
	}

	public function mayEditOwnSettings(): bool
	{
		return $this->mayEditOwnSettings;
	}
}
