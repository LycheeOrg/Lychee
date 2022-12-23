<?php

namespace App\Http\Requests\Settings;

use App\Facades\Lang;
use Illuminate\Validation\Rule;

class SetLangSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'lang';

	public function rules(): array
	{
		return [
			'lang' => ['required', 'string', Rule::in(Lang::get_lang_available())],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE];
	}
}
