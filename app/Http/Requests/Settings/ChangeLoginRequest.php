<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\Session\LoginRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

class ChangeLoginRequest extends LoginRequest
{
	const OLD_USERNAME_ATTRIBUTE = 'oldUsername';
	const OLD_PASSWORD_ATTRIBUTE = 'oldPassword';

	protected ?string $oldUsername = null;
	protected ?string $oldPassword = null;

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