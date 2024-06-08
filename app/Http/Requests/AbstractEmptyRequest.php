<?php

namespace App\Http\Requests;

abstract class AbstractEmptyRequest extends BaseApiRequest
{
	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [];
	}

	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
