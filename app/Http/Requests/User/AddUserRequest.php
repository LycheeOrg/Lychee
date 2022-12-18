<?php

namespace App\Http\Requests\User;

use App\Contracts\Http\Requests\RequestAttribute;
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
