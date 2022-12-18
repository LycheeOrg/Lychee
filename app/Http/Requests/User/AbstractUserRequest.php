<?php

namespace App\Http\Requests\User;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Policies\UserPolicy;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Gate;

abstract class AbstractUserRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	public const MAY_UPLOAD_ATTRIBUTE = 'may_upload';
	public const IS_LOCKED_ATTRIBUTE = 'is_locked';

	protected bool $mayUpload = false;
	protected bool $isLocked = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// This should always return true, because we already check that the
		// request is made by an admin during authentication (see
		// `routes/web.php`).
		// But better safe than sorry.
		return Gate::check(UserPolicy::IS_ADMIN);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(false)],
			self::MAY_UPLOAD_ATTRIBUTE => 'present|boolean',
			self::IS_LOCKED_ATTRIBUTE => 'present|boolean',
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
		$this->mayUpload = static::toBoolean($values[self::MAY_UPLOAD_ATTRIBUTE]);
		$this->isLocked = static::toBoolean($values[self::IS_LOCKED_ATTRIBUTE]);
	}

	public function mayUpload(): bool
	{
		return $this->mayUpload;
	}

	public function isLocked(): bool
	{
		return $this->isLocked;
	}
}