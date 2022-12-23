<?php

namespace App\Http\Requests\Settings;

class SetLocationShowPublicSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'location_show_public';

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
