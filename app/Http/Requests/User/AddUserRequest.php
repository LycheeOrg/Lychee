<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\HasPassword;
use App\Rules\PasswordRule;

class AddUserRequest extends AbstractUsernamePasswordRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules[HasPassword::PASSWORD_ATTRIBUTE] = ['required', new PasswordRule(false)];

		return $rules;
	}
}
