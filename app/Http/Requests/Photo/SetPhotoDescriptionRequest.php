<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\RuleSets\Photo\SetPhotoDescriptionRuleSet;
use App\Models\Photo;

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
		return SetPhotoDescriptionRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::findOrFail($values[RequestAttribute::PHOTO_ID_ATTRIBUTE]);
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
	}
}
