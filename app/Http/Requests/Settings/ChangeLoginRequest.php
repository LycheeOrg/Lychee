<?php

namespace App\Http\Requests\Settings;

use App\Facades\AccessControl;
use App\Http\Requests\Session\LoginRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

class ChangeLoginRequest extends LoginRequest
{
	public const OLD_USERNAME_ATTRIBUTE = 'oldUsername';
	public const OLD_PASSWORD_ATTRIBUTE = 'oldPassword';

	protected ?string $oldUsername = null;
	protected ?string $oldPassword = null;

	/**
	 * Determines if the user is authorized to make this request.
	 *
	 * TODO: This method need to be rewritten after {@link \App\Actions\Settings\Login::do()} has been refactored.
	 *
	 * Normally, the request to change a user's password should
	 * only be authorized for admin or non-locked users.
	 * However, at the moment the method
	 * {@link \App\Actions\Settings\Login::do()} is a "god" method and serves
	 * three totally different use-cases mixed into one method (see comment
	 * there).
	 * We cannot reliably determine if the request is authorized without
	 * knowing which of the use-case applies and thus without repeating
	 * most of the logic of {@link \App\Actions\Settings\Login::do()}.
	 * Hence, we authorize this request unconditionally and assume that
	 * {@link \App\Actions\Settings\Login::do()} enforces correct
	 * authorization.
	 *
	 * @return bool always true
	 */
	public function authorize(): bool
	{
		/*return AccessControl::is_logged_in() && (
			AccessControl::is_admin() ||
			!AccessControl::user()->is_locked
		);*/

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules[self::OLD_USERNAME_ATTRIBUTE] = ['sometimes', new UsernameRule()];
		$rules[self::OLD_PASSWORD_ATTRIBUTE] = ['sometimes', new PasswordRule(false)];

		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		parent::processValidatedValues($values, $files);
		$this->oldUsername = $values[self::OLD_USERNAME_ATTRIBUTE] ?? null;
		if (array_key_exists(self::OLD_PASSWORD_ATTRIBUTE, $values)) {
			// See {@link HasPasswordTrait::password()} for an explanation
			// of the semantic difference between `null` and `''`.
			$this->oldPassword = $values[self::OLD_PASSWORD_ATTRIBUTE] ?? '';
		} else {
			$this->oldPassword = null;
		}
	}

	/**
	 * @return string|null
	 */
	public function oldUsername(): ?string
	{
		return $this->oldUsername;
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