<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\LandingFeaturedItem;

use App\Enum\LandingFeaturedItemType;
use App\Http\Requests\BaseApiRequest;
use App\Models\LandingFeaturedItem;
use App\Models\User;
use App\Rules\LandingFeaturedItemExistsRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class PatchLandingFeaturedItemRequest extends BaseApiRequest
{
	public LandingFeaturedItem $landing_featured_item;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['landing_featured_item_id' => $this->route('landingFeaturedItem')]);
	}

	public function rules(): array
	{
		return [
			'landing_featured_item_id' => ['required', 'string'],
			'item_type' => ['sometimes', 'required', 'string', new Enum(LandingFeaturedItemType::class)],
			'item_id' => ['required_with:item_type', 'string', new LandingFeaturedItemExistsRule(
				LandingFeaturedItem::find($this->route('landingFeaturedItem'))?->item_type->value,
			)],
			'sort_order' => ['sometimes', 'integer'],
			'enabled' => ['sometimes', 'boolean'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->landing_featured_item = LandingFeaturedItem::findOrFail($values['landing_featured_item_id']);
	}
}
