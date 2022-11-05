<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPassword;
use App\Http\Requests\Contracts\HasUsername;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Gate;

class ChangeLoginRequest extends BaseApiRequest implements HasPassword
{
	use HasPasswordTrait;

	public const OLD_PASSWORD_ATTRIBUTE = 'oldPassword';

	protected string $oldPassword;
	protected ?string $username = null;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT, [User::class]);
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

		// We do not allow '' as a username. So any such input will be cast to null
		if (array_key_exists(HasUsername::USERNAME_ATTRIBUTE, $values)) {
			$this->username = trim($values[HasUsername::USERNAME_ATTRIBUTE]);
			$this->username = $this->username === '' ? null : $this->username;
		} else {
			$this->username = null;
		}
	}

	/**
	 * Returns the previous password.
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

	/**
	 * Return the new username chosen.
	 * if Username is null, this means that the user does not want to update it.
	 *
	 * @return ?string
	 */
	public function username(): ?string
	{
		return $this->username;
	}
}