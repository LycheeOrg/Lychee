<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

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
