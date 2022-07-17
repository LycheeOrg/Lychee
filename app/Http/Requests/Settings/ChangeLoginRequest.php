<?php

namespace App\Http\Requests\Settings;

use App\Auth\Authorization;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Contracts\HasUsername;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

class ChangeLoginRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	public const OLD_PASSWORD_ATTRIBUTE = 'oldPassword';

	protected ?string $oldPassword = null;

	/**
	 * Determines if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return Authorization::check() && (
			Authorization::isAdmin() ||
			!Authorization::userOrFail()->is_locked
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasUsername::USERNAME_ATTRIBUTE => ['sometimes', new UsernameRule()],
			HasPassword::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
			self::OLD_PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->password = $values[HasPassword::PASSWORD_ATTRIBUTE];
		$this->oldPassword = $values[self::OLD_PASSWORD_ATTRIBUTE];

		if (array_key_exists(HasUsername::USERNAME_ATTRIBUTE, $values)) {
			$this->username = $values[HasUsername::USERNAME_ATTRIBUTE];
			$this->username = $this->username === '' ? null : $this->username;
		} else {
			$this->username = null;
		}
	}

	/**
	 * Returns the previous (old) password, if available.
	 *
	 * See {@link HasPasswordTrait::password()} for an explanation of the
	 * semantic difference between the return values `null` and `''`.
	 *
	 * @return string|null
	 */
	public function oldPassword(): ?string
	{
		return $this->oldPassword;
	}
}