<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

class SetLocationShowSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'location_show';

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
