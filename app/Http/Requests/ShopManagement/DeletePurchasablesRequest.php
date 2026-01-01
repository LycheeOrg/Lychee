<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\Purchasable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DeletePurchasablesRequest extends BaseApiRequest
{
	/** @var Collection<Purchasable> */
	public Collection $purchasables;

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
			RequestAttribute::PURCHASABLE_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PURCHASABLE_IDS_ATTRIBUTE . '.*' => 'required|integer',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,int> $purchasable_ids */
		$purchasable_ids = $values[RequestAttribute::PURCHASABLE_IDS_ATTRIBUTE];
		$this->purchasables = Purchasable::findOrFail($purchasable_ids);
	}
}
