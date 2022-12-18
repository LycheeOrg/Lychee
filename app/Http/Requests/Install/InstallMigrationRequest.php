<?php

namespace App\Http\Requests\Install;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Http\Request;

class InstallMigrationRequest extends Request implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * This Request is only available if the application is not installed yet.
	 * Thus, there's no authorization check here.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return true;
	}
}
