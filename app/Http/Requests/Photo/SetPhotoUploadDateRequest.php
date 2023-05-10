<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasDate;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasDateTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\RuleSets\Photo\SetPhotoUploadDateRuleSet;
use App\Models\Photo;
use Illuminate\Support\Carbon;

class SetPhotoUploadDateRequest extends BaseApiRequest implements HasPhoto, HasDate
{
	use HasPhotoTrait;
	use HasDateTrait;
	use AuthorizeCanEditPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotoUploadDateRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::findOrFail($photoID);
		$this->date = Carbon::parse($values[RequestAttribute::DATE_ATTRIBUTE]);
	}
}
