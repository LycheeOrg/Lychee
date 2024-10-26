<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class RegisterRequest extends BaseApiRequest
{
	private \SensitiveParameterValue $key;

	public function rules(): array
	{
		return [
			'key' => ['required', 'string', 'max:255'],
		];
	}

	protected function processValidatedValues(
		#[\SensitiveParameter]
		array $values,
		array $files): void
	{
		$this->key = new \SensitiveParameterValue($values['key']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function key(): \SensitiveParameterValue
	{
		return $this->key;
	}
}
