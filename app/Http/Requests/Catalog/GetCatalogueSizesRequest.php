<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Catalog;

use App\Http\Requests\BaseApiRequest;
use App\Models\Purchasable;

class GetCatalogueSizesRequest extends BaseApiRequest
{
	public Purchasable $purchasable;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			'purchasable_id' => $this->route('purchasable_id'),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'purchasable_id' => 'required|integer',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->purchasable = Purchasable::findOrFail($values['purchasable_id']);
	}
}
