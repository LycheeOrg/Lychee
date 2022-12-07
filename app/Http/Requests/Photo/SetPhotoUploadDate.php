<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasDate;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasDateTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;
use Illuminate\Support\Carbon;

class SetPhotoUploadDate extends BaseApiRequest implements HasPhoto, HasDate
{
	use HasPhotoTrait;
	use HasDateTrait;
	use AuthorizeCanEditPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DATE_ATTRIBUTE => 'required|date',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->findOrFail($values[RequestAttribute::PHOTO_ID_ATTRIBUTE]);
		$this->date = Carbon::parse($values[RequestAttribute::DATE_ATTRIBUTE]);
	}
}
