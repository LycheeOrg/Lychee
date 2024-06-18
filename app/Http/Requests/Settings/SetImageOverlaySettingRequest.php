<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enum\ImageOverlayType;
use Illuminate\Validation\Rules\Enum;

class SetImageOverlaySettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'image_overlay_type';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(ImageOverlayType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = ImageOverlayType::from($values[self::ATTRIBUTE]);
	}
}
