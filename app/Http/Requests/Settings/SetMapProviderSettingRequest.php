<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class SetMapProviderSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'map_provider';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', 'string', Rule::in([
				'Wikimedia',
				'OpenStreetMap.org',
				'OpenStreetMap.de',
				'OpenStreetMap.fr',
				'RRZE',
			])],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE];
	}
}
