<?php

namespace App\Http\Requests\Users;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Http\RuleSets\Users\AddUserRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class AddUserRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

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
		return AddUserRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
		$this->mayUpload = static::toBoolean($values[RequestAttribute::MAY_UPLOAD_ATTRIBUTE]);
		$this->mayEditOwnSettings = static::toBoolean($values[RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE]);
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
