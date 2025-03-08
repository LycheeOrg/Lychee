<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Timeline;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Configs;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class IdOrDatedTimelineRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	public ?Carbon $date = null;

	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['nullable', 'sometimes', new RandomIDRule(false)],
			RequestAttribute::DATE_ATTRIBUTE => ['nullable', 'date', 'sometimes'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !Configs::getValueAsBool('timeline_photos_public')) {
			return false;
		}

		return Configs::getValueAsBool('timeline_page_enabled');
	}

	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// We only set this one if it is not null
		if (isset($values[RequestAttribute::DATE_ATTRIBUTE])) {
			$this->date = Carbon::parse($values[RequestAttribute::DATE_ATTRIBUTE]);
		}

		if (isset($values[RequestAttribute::PHOTO_ID_ATTRIBUTE])) {
			/** @var string $photoId */
			$photoId = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
			$this->photo = Photo::query()->findOrFail($photoId);
		}
	}
}
