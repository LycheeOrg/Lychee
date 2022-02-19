<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

/**
 * Class SetPhotosStarredRequest.
 *
 * Note, the class is a misnomer.
 * Actually, the related request does not set the `is_starred` attribute, but
 * toggles it.
 */
class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotosWrite($this->photos);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail($values[HasPhotos::PHOTO_IDS_ATTRIBUTE]);
	}
}
