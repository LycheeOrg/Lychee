<?php

namespace App\Http\Requests\Settings;

class SetMapDisplaySettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'map_display';

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
