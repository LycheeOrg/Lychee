<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

class SetNewPhotosNotificationSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'new_photos_notification';

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
