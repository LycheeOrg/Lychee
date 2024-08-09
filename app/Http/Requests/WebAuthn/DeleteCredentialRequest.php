<?php

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteCredentialRequest extends BaseApiRequest
{
	private string $id;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT, [User::class]);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::ID_ATTRIBUTE => 'required|string',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->id = $values[RequestAttribute::ID_ATTRIBUTE];
	}

	public function getId(): string
	{
		return $this->id;
	}
}
