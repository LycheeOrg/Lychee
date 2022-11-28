<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasDescription;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;

class SetPhotoDescriptionRequest extends BaseApiRequest implements HasPhoto, HasDescription
{
	use HasPhotoTrait;
	use HasDescriptionTrait;
	use AuthorizeCanEditPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhoto::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasDescription::DESCRIPTION_ATTRIBUTE => ['required', new DescriptionRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->without(['size_variants'])->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
		$this->description = $values[HasDescription::DESCRIPTION_ATTRIBUTE];
	}
}
