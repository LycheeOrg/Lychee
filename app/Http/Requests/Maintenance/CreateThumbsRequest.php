<?php

namespace App\Http\Requests\Maintenance;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\SizeVariantType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateThumbsRequest extends BaseApiRequest
{
	private SizeVariantType $kind;

	public function rules(): array
	{
		return [
			RequestAttribute::SIZE_VARIANT_ATTRIBUTE => [
				'required',
				'string',
				Rule::in([
					SizeVariantType::SMALL->value,
					SizeVariantType::SMALL2X->value,
					SizeVariantType::MEDIUM->value,
					SizeVariantType::MEDIUM2X->value]),
			],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->kind = SizeVariantType::from($values[RequestAttribute::SIZE_VARIANT_ATTRIBUTE]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function kind(): SizeVariantType
	{
		return $this->kind;
	}
}
