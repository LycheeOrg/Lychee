<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\UserManagement;

use App\Contracts\Http\Requests\HasNote;
use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasQuotaKB;
use App\Contracts\Http\Requests\HasUser;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasNoteTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasQuotaKBTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\BooleanRequireSupportRule;
use App\Rules\IntegerIDRule;
use App\Rules\IntegerRequireSupportRule;
use App\Rules\PasswordRule;
use App\Rules\StringRequireSupportRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Gate;

class SetUserSettingsRequest extends BaseApiRequest implements HasUsername, HasPassword, HasUser, HasQuotaKB, HasNote
{
	use HasUsernameTrait;
	use HasPasswordTrait;
	use HasUserTrait;
	use HasQuotaKBTrait;
	use HasNoteTrait;

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
		return [
			RequestAttribute::ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule(), 'min:1'],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(false)],
			RequestAttribute::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE => 'present|boolean',
			RequestAttribute::HAS_QUOTA_ATTRIBUTE => ['sometimes', 'boolean', new BooleanRequireSupportRule(false, $this->verify)],
			RequestAttribute::QUOTA_ATTRIBUTE => ['sometimes', 'int', new IntegerRequireSupportRule(0, $this->verify)],
			RequestAttribute::NOTE_ATTRIBUTE => ['sometimes', 'nullable', 'string', new StringRequireSupportRule('', $this->verify)],
		];
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
			$this->password = null;
		}
		$this->mayUpload = static::toBoolean($values[RequestAttribute::MAY_UPLOAD_ATTRIBUTE]);
		$this->mayEditOwnSettings = static::toBoolean($values[RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE]);
		/** @var int $userID */
		$userID = $values[RequestAttribute::ID_ATTRIBUTE];
		$this->user2 = User::query()->findOrFail($userID);
		$has_quota = static::toBoolean($values[RequestAttribute::HAS_QUOTA_ATTRIBUTE] ?? false);
		$this->quota_kb = $has_quota ? intval($values[RequestAttribute::QUOTA_ATTRIBUTE]) : null;
		$this->note = $values[RequestAttribute::NOTE_ATTRIBUTE] ?? '';
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
