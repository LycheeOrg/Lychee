<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement\PrintSize;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\BaseApiRequest;
use App\Models\PrintSize;
use App\Models\PurchasablePrintSize;
use Illuminate\Support\Facades\Auth;

class DeletePrintSizeRequest extends BaseApiRequest
{
	public PrintSize $print_size;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var int|null $user_id */
		$user_id = Auth::id();
		if ($user_id === null) {
			return false;
		}

		return $this->configs()->getValueAsInt('owner_id') === $user_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'print_size_id' => 'required|integer',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->print_size = PrintSize::findOrFail($values['print_size_id']);

		if (PurchasablePrintSize::where('print_size_id', $this->print_size->id)->exists()) {
			throw new LycheeLogicException('Cannot delete a print size that is still assigned to purchasable items');
		}
	}
}
