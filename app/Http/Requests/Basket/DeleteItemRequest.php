<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use Illuminate\Http\UploadedFile;

class DeleteItemRequest extends BaseBasketRequest
{
	/**
	 * @var int
	 */
	public int $item_id;

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		// Anyone can delete items from their own basket (session-verified)
		return $this->order?->items?->contains(fn ($item) => $item->id === $this->item_id) ?? false;
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'item_id' => ['required', 'integer'],
		];
	}

	/**
	 * Process the validated values.
	 *
	 * @param array<string,mixed>        $values
	 * @param array<string,UploadedFile> $files
	 *
	 * @return void
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->item_id = (int) ($values['item_id'] ?? 0);
	}
}
