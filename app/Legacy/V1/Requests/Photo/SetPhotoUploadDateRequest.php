<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Photo\SetPhotoUploadDateRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasDate;
use App\Legacy\V1\Contracts\Http\Requests\HasPhoto;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Legacy\V1\Requests\Traits\HasDateTrait;
use App\Legacy\V1\Requests\Traits\HasPhotoTrait;
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
		$this->photo = Photo::query()->findOrFail($photoID);
		$this->date = Carbon::parse($values[RequestAttribute::DATE_ATTRIBUTE]);
	}
}
