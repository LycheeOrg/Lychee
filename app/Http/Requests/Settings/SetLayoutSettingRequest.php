<?php

namespace App\Http\Requests\Settings;

use App\Enum\PhotoLayoutType;
use Illuminate\Validation\Rules\Enum;

class SetLayoutSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'layout';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(PhotoLayoutType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = PhotoLayoutType::from($values[self::ATTRIBUTE]);
	}
}
