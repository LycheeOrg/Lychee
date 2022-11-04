<?php

namespace App\Http\Requests\Settings;

class SetPublicSearchSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'public_search';

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
