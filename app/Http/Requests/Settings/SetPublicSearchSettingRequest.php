<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

class SetPublicSearchSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'search_public';

	public function rules(): array
	{
		return [
			'public_search' => 'required_without:search_public|boolean', // legacy
			'search_public' => 'required_without:public_search|boolean', // new value
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = self::toBoolean($values['search_public'] ?? $values['public_search']);
	}
}
