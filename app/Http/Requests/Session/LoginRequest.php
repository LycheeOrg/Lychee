<?php

namespace App\Http\Requests\Session;

use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Session\LoginRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasPassword;
use App\Legacy\V1\Contracts\Http\Requests\HasUsername;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPasswordTrait;
use App\Legacy\V1\Requests\Traits\HasUsernameTrait;

class LoginRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return LoginRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
	}
}
