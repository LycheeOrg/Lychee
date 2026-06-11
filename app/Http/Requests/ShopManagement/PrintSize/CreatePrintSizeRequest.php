<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement\PrintSize;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

class CreatePrintSizeRequest extends BaseApiRequest
{
	public string $label;
	public int $width;
	public int $height;
	public string $unit;
	public ?string $paper_type;
	public bool $is_active;

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
			'label' => 'required|string|max:100',
			'width' => 'required|integer|min:1',
			'height' => 'required|integer|min:1',
			'unit' => 'required|string|in:cm,inch',
			'paper_type' => 'nullable|string|max:100',
			'is_active' => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->label = $values['label'];
		$this->width = (int) $values['width'];
		$this->height = (int) $values['height'];
		$this->unit = $values['unit'];
		$this->paper_type = $values['paper_type'] ?? null;
		$this->is_active = self::toBoolean($values['is_active']);
	}
}
