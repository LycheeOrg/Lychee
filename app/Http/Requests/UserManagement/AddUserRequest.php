<?php

namespace App\Http\Requests\UserManagement;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Contracts\HasUsername;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Gate;

class AddUserRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	public const MAY_UPLOAD_ATTRIBUTE = 'may_upload';
	public const MAY_EDIT_OWN_SETTINGS_ATTRIBUTE = 'may_edit_own_settings';

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
			HasUsername::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			HasPassword::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
			self::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			self::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE => 'present|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[HasUsername::USERNAME_ATTRIBUTE];
		$this->password = $values[HasPassword::PASSWORD_ATTRIBUTE];
		$this->mayUpload = static::toBoolean($values[self::MAY_UPLOAD_ATTRIBUTE]);
		$this->mayEditOwnSettings = static::toBoolean($values[self::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE]);
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
