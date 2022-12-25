<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class SetImageOverlaySettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'image_overlay_type';

	public function rules(): array
	{
		return [self::ATTRIBUTE => [
			'required',
			'string',
			Rule::in(['none', 'desc', 'date', 'exif']),
		],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE];
	}
}
