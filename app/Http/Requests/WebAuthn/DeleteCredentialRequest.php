<?php

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;
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
		return Gate::check(UserPolicy::CAN_USE_2FA, [User::class]);
	}

	public function rules(): array
	{
		return ['id' => 'required|string'];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->id = $values['id'];
	}

	public function getId(): string
	{
		return $this->id;
	}
}
