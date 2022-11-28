<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasDate;
use App\Http\Requests\Contracts\HasPhoto;
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
			HasPhoto::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasDate::DATE_ATTRIBUTE => 'required|date',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->without(['size_variants'])->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
		$this->date = Carbon::parse($values[HasDate::DATE_ATTRIBUTE]);
	}
}
