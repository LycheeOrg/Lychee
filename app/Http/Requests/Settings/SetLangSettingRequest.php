<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class SetLangSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'lang';

	public function rules(): array
	{
		return [
			'lang' => ['required', 'string', Rule::in(config('app.supported_locale'))],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE];
	}
}
