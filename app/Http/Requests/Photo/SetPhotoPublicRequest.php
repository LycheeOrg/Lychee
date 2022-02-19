<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

/**
 * Class SetPhotoPublicRequest.
 *
 * Note, the class is a misnomer.
 * Actually, the related request does not set the `is_public` attribute, but
 * toggles it.
 */
class SetPhotoPublicRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite($this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhoto::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
	}
}
