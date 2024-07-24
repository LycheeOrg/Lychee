<?php

namespace App\Http\Requests\Profile;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class SetEmailRequest extends BaseApiRequest
{
	protected ?string $email = null;

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
			RequestAttribute::EMAIL_ATTRIBUTE => 'present|nullable|email:rfc,dns|max:100',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE];
	}

	public function email(): ?string
	{
		return $this->email;
	}
}
