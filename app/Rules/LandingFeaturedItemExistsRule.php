<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

use App\Enum\LandingFeaturedItemType;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that `item_id` references an existing Photo/Album matching the
 * sibling `item_type` field (or an explicit fallback, used by PATCH when
 * `item_type` is not part of the payload).
 */
final class LandingFeaturedItemExistsRule implements DataAwareRule, ValidationRule
{
	/** @var array<string,mixed> */
	private array $data = [];

	public function __construct(private readonly ?string $fallback_item_type = null)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function setData(array $data): static
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		$type = $this->data['item_type'] ?? $this->fallback_item_type;

		$exists = match ($type) {
			LandingFeaturedItemType::PHOTO->value => Photo::query()->whereKey($value)->exists(),
			LandingFeaturedItemType::ALBUM->value => Album::query()->whereKey($value)->exists(),
			default => false,
		};

		if (!$exists) {
			$fail('The selected :attribute does not reference an existing item of the given item_type.');
		}
	}
}
