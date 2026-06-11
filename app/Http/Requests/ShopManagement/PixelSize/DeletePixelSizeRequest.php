<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement\PixelSize;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\BaseApiRequest;
use App\Models\PixelSize;
use App\Models\PurchasablePixelSize;
use Illuminate\Support\Facades\Auth;

class DeletePixelSizeRequest extends BaseApiRequest
{
	public PixelSize $pixel_size;

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
			'pixel_size_id' => 'required|integer',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->pixel_size = PixelSize::findOrFail($values['pixel_size_id']);

		if (PurchasablePixelSize::where('pixel_size_id', $this->pixel_size->id)->exists()) {
			throw new LycheeLogicException('Cannot delete a pixel size that is still assigned to purchasable items');
		}
	}
}
