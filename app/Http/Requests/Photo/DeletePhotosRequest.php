<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasFromId;
use App\Contracts\Http\Requests\HasPhotoIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasFromIdTrait;
use App\Http\Requests\Traits\HasPhotoIdsTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class DeletePhotosRequest extends BaseApiRequest implements HasPhotoIds, HasFromId
{
	use HasPhotoIdsTrait;
	use HasFromIdTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_DELETE_BY_ID, [Photo::class, $this->photoIds()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::FROM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the photos anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->from_id = $values[RequestAttribute::FROM_ID_ATTRIBUTE];
	}
}
