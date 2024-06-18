<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

class SetCSSSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'css';

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [self::ATTRIBUTE => 'present|nullable|string'];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE] ?? '';
	}
}
