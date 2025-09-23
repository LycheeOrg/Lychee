<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\ShopManagement;

use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Models\Configs;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;

class ListPurchasablesRequest extends BaseApiRequest implements HasAlbumIds
{
	use HasAlbumIdsTrait;

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

		return Configs::getValueAsInt('owner_id') === $user_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => [new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? [];
	}
}
