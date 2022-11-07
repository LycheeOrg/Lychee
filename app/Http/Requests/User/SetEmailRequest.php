<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class SetEmailRequest extends BaseApiRequest
{
	public const EMAIL_ATTRIBUTE = 'email';

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
			self::EMAIL_ATTRIBUTE => 'present|nullable|email:rfc,dns|max:100',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->email = $values[self::EMAIL_ATTRIBUTE];
	}

	public function email(): ?string
	{
		return $this->email;
	}
}
