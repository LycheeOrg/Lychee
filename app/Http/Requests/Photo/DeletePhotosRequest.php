<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Rules\RandomIDRule;

class DeletePhotosRequest extends BaseApiRequest implements HasPhotoIDs
{
	use HasPhotoIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotosWriteByIDs($this->photoIDs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the photos anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->photoIDs = $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE];
	}
}
