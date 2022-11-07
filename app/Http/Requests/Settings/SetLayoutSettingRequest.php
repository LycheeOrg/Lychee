<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class SetLayoutSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'layout';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', Rule::in([0, 1, 2])],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = (int) $values[self::ATTRIBUTE];
	}
}
