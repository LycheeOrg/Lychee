<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasDescription;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;

class SetPhotoDescriptionRequest extends BaseApiRequest implements HasPhotoID, HasDescription
{
	use HasPhotoIDTrait;
	use HasDescriptionTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite([$this->photoID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasDescription::DESCRIPTION_ATTRIBUTE => ['required', new DescriptionRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
		$this->description = $values[HasDescription::DESCRIPTION_ATTRIBUTE];
	}
}
