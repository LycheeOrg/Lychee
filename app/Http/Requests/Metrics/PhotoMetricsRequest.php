<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Metrics;

use App\Contracts\Http\Requests\HasFromId;
use App\Contracts\Http\Requests\HasPhotoIds;
use App\Contracts\Http\Requests\HasVisitorId;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasFromIdTrait;
use App\Http\Requests\Traits\HasPhotoIdsTrait;
use App\Http\Requests\Traits\HasVisitorIdTrait;
use App\Rules\RandomIDRule;

class PhotoMetricsRequest extends BaseApiRequest implements HasPhotoIds, HasVisitorId, HasFromId
{
	use HasPhotoIdsTrait;
	use HasFromIdTrait;
	use HasVisitorIdTrait;

	// No need to authorize this request as it is only used for metrics purposes
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::FROM_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->from_id = $values[RequestAttribute::FROM_ID_ATTRIBUTE] ?? null;
	}
}
