<?php

namespace App\Http\Requests\Users;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUser;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\IntegerIDRule;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Gate;

class SetUserSettingsRequest extends BaseApiRequest implements HasUsername, HasPassword, HasUser
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
		return [
			RequestAttribute::ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule(), 'min:1'],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(false)],
			RequestAttribute::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			RequestAttribute::MAY_EDIT_OWN_SETTINGS_ATTRIBUTE => 'present|boolean',
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
