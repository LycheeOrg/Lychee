<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Request to fetch albums containing a photo.
 *
 * Supports both authenticated and guest users.
 * Authorization uses PhotoPolicy::CAN_SEE to verify the user can see the photo.
 */
class GetPhotoAlbumsRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_SEE, [Photo::class, $this->photo]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * Merge route parameter into request data for validation.
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::PHOTO_ID_ATTRIBUTE => $this->route(RequestAttribute::PHOTO_ID_ATTRIBUTE),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $photo_id */
		$photo_id = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];

		$this->photo = Photo::query()
			->with(['albums'])
			->findOrFail($photo_id);
	}
}
