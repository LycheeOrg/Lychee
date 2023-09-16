<?php

namespace App\Http\Requests\Settings;

use App\Enum\AlbumLayoutType;
use Illuminate\Validation\Rules\Enum;

class SetLayoutSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'layout';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(AlbumLayoutType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = AlbumLayoutType::from($values[self::ATTRIBUTE]);
	}
}
