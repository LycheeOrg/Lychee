<?php

namespace App\Legacy\V1\Requests\Settings;

class SetLocationDecodingSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'location_decoding';

	public function rules(): array
	{
		return [self::ATTRIBUTE => 'required|boolean'];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = self::toBoolean($values[self::ATTRIBUTE]);
	}
}
