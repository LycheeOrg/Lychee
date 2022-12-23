<?php

namespace App\Http\Requests\Settings;

use App\Rules\LicenseRule;

class SetDefaultLicenseSettingRequest extends AbstractSettingRequest
{
	public function rules(): array
	{
		return ['license' => ['required', new LicenseRule()]];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = 'default_license';
		$this->value = $values['license'];
	}
}
