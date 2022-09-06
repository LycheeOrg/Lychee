<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

/**
 * Class SetPhotosStarredRequest.
 */
class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;
	use AuthorizeCanEditPhotosTrait;

	public const IS_STARRED_ATTRIBUTE = 'is_starred';

	protected bool $isStarred = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			self::IS_STARRED_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail($values[HasPhotos::PHOTO_IDS_ATTRIBUTE]);
		$this->isStarred = static::toBoolean($values[self::IS_STARRED_ATTRIBUTE]);
	}

	public function isStarred(): bool
	{
		return $this->isStarred;
	}
}
