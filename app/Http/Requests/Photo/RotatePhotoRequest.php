<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rule;

class RotatePhotoRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;
	use AuthorizeCanEditPhotoTrait;

	protected int $direction;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DIRECTION_ATTRIBUTE => ['required', Rule::in([-1, 1])],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()
			->with(['size_variants'])
			->findOrFail($photoID);
		$this->direction = intval($values[RequestAttribute::DIRECTION_ATTRIBUTE]);
	}

	public function direction(): int
	{
		return $this->direction;
	}
}
