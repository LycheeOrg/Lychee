<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasDescription;
use App\Legacy\V1\Contracts\Http\Requests\HasPhoto;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Legacy\V1\Requests\Traits\HasDescriptionTrait;
use App\Legacy\V1\Requests\Traits\HasPhotoTrait;
use App\Legacy\V1\RuleSets\Photo\SetPhotoDescriptionRuleSet;
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
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()->findOrFail($photoID);
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
	}
}
