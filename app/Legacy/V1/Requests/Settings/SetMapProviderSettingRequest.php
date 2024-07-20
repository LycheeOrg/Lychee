<?php

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\MapProviders;
use Illuminate\Validation\Rules\Enum;

class SetMapProviderSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'map_provider';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(MapProviders::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = MapProviders::from($values[self::ATTRIBUTE]);
	}
}
