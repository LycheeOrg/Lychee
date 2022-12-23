<?php

namespace App\Http\Requests\Install;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Http\FormRequest;

class InstallMigrationRequest extends FormRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', 'confirmed', new PasswordRule(false)],
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

	public function passedValidation()
	{
		$values = $this->validated();
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
	}
}
