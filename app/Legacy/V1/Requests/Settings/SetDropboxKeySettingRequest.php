<?php

namespace App\Legacy\V1\Requests\Settings;

class SetDropboxKeySettingRequest extends AbstractSettingRequest
{
	public function rules(): array
	{
		return ['key' => 'present|string|nullable'];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = 'dropbox_key';
		$this->value = $values['key'];
	}
}
