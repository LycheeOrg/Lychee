<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Contracts\RequestAttribute;
use App\Rules\PasswordRule;

class AddUserRequest extends AbstractUserRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules[RequestAttribute::PASSWORD_ATTRIBUTE] = ['required', new PasswordRule(false)];

		return $rules;
	}
}
