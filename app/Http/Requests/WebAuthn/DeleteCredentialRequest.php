<?php

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteCredentialRequest extends BaseApiRequest
{
	private string $id;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_USE_2FA, Configs::class);
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
