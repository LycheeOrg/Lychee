<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Timeline;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Configs;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
		$granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
		$format = $granularity->format();

		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['nullable', 'sometimes', new RandomIDRule(false)],
			// We validate the date format directly instead of
			// Rule::date()->format($format) <- this tries to validate for the date first, then the string.
			// For some stupid reason, a single year like 2024 is not recognized as a date.
			RequestAttribute::DATE_ATTRIBUTE => ['nullable', 'sometimes', 'date_format:' . $format],
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
			$granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
			$format = $granularity->format();

			// Validate format.
			$this->date = Carbon::createFromFormat($format, $values[RequestAttribute::DATE_ATTRIBUTE]);
		}

		if (isset($values[RequestAttribute::PHOTO_ID_ATTRIBUTE])) {
			/** @var string $photo_id */
			$photo_id = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
			$this->photo = Photo::query()->findOrFail($photo_id);
		}
	}
}
