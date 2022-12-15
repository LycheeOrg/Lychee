<?php

namespace App\Http\Requests\Settings;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
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
	 * Determines if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT_SETTINGS, User::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['sometimes', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
			self::OLD_PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
		$this->oldPassword = $values[self::OLD_PASSWORD_ATTRIBUTE];

		// We do not allow '' as a username. So any such input will be cast to null
		if (array_key_exists(RequestAttribute::USERNAME_ATTRIBUTE, $values)) {
			$this->username = trim($values[RequestAttribute::USERNAME_ATTRIBUTE]);
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