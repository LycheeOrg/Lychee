<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;

class RenamePhotoRequest extends BaseApiRequest implements HasTitle, HasPhoto
{
	use HasTitleTrait;
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT, $this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()->findOrFail($photoID);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
